<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\CronJob;
use App\Lib\CurlRequest;
use App\Constants\Status;
use App\Models\CronJobLog;
use App\Models\Transaction;
use App\Models\AccountListing;
use App\Models\BiddingListing;

class CronController extends Controller
{
    public function cron()
    {
        $general            = gs();
        $general->last_cron = now();
        $general->save();

        $crons = CronJob::with('schedule');

        if (request()->alias) {
            $crons->where('alias', request()->alias);
        } else {
            $crons->where('next_run', '<', now())->where('is_running', Status::YES);
        }
        $crons = $crons->get();
        foreach ($crons as $cron) {
            $cronLog              = new CronJobLog();
            $cronLog->cron_job_id = $cron->id;
            $cronLog->start_at    = now();
            if ($cron->is_default) {
                $controller = new $cron->action[0];
                try {
                    $method = $cron->action[1];
                    $controller->$method();
                } catch (\Exception $e) {
                    $cronLog->error = $e->getMessage();
                }
            } else {
                try {
                    CurlRequest::curlContent($cron->url);
                } catch (\Exception $e) {
                    $cronLog->error = $e->getMessage();
                }
            }
            $cron->last_run = now();
            $cron->next_run = now()->addSeconds($cron->schedule->interval);
            $cron->save();

            $cronLog->end_at = $cron->last_run;

            $startTime         = Carbon::parse($cronLog->start_at);
            $endTime           = Carbon::parse($cronLog->end_at);
            $diffInSeconds     = $startTime->diffInSeconds($endTime);
            $cronLog->duration = $diffInSeconds;
            $cronLog->save();
        }
        if (request()->target == 'all') {
            $notify[] = ['success', 'Cron executed successfully'];
            return back()->withNotify($notify);
        }
        if (request()->alias) {
            $notify[] = ['success', keyToTitle(request()->alias) . ' executed successfully'];
            return back()->withNotify($notify);
        }
    }


    public function auctionResult()
    {
        try {
            $accountListings = AccountListing::active()->pricingModelAuction()->where('auction_deadline', '<', today())->get();

            foreach ($accountListings as $accountListing) {

                $biddingWin    = BiddingListing::where('account_listing_id', $accountListing->id)->orderBy('amount', 'desc')->first();
                if (!$biddingWin) continue;
                $biddingLosses = BiddingListing::where('account_listing_id', $accountListing->id)->where('id', '!=', $biddingWin->id)->get();

                // For win
                if ($biddingWin) {
                    $accountList            = AccountListing::find($accountListing->id);
                    $accountList->buyer_id  = $biddingWin->user_id;
                    $accountList->buy_price = $biddingWin->amount;
                    $accountList->status    = Status::LISTING_SOLD;
                    $accountList->save();

                    if (userNotifyPermission($accountList->buyer, 'buy')) {
                        notify($biddingWin->user, 'ACCOUNT_BUYING', [
                            'title'         => $accountListing->title,
                            'buy_price'     => showAmount($biddingWin->amount,currencyFormat:false),
                            'pricing_model' => $accountListing->pricing_model == Status::AUCTION ? 'Auction' : 'Fixed',
                        ]);
                    }

                    $sellerUser           = User::find($biddingWin->accountListing->user_id);
                    $sellerUser->balance += $biddingWin->amount;
                    $sellerUser->save();

                    $transaction               = new Transaction();
                    $transaction->user_id      = $sellerUser->id;
                    $transaction->amount       = $biddingWin->amount;
                    $transaction->post_balance = $sellerUser->balance;
                    $transaction->charge       = 0;
                    $transaction->trx_type     = '+';
                    $transaction->details      = 'Account Sale';
                    $transaction->trx          = getTrx();
                    $transaction->remark       = 'account_sell';
                    $transaction->save();

                    $totalCharge = gs('fixed_charge') + ((gs('percentage_charge') / 100) * $biddingWin->amount);

                    $sellerUser->balance -= $totalCharge;
                    $sellerUser->save();

                    $transaction               = new Transaction();
                    $transaction->user_id      = $sellerUser->id;
                    $transaction->amount       = $totalCharge;
                    $transaction->post_balance = $sellerUser->balance;
                    $transaction->charge       = 0;
                    $transaction->trx_type     = '-';
                    $transaction->details      = 'Charge For Sale Account';
                    $transaction->trx          = getTrx();
                    $transaction->remark       = 'seller_fee';
                    $transaction->save();

                    if (userNotifyPermission($accountList->user, 'sell')) {
                        notify($sellerUser, 'ACCOUNT_SELLING', [
                            'title'         => $accountListing->title,
                            'sell_price'    => showAmount($accountListing->sell_price,currencyFormat:false),
                            'seller_fee'        => showAmount($totalCharge,currencyFormat:false),
                            'pricing_model' => $accountListing->pricing_model == Status::AUCTION ? 'Auction' : 'Fixed',
                        ]);
                    }
                }

                // For Loss
                foreach ($biddingLosses as $biddingLoss) {
                    $lossUser           = User::find($biddingLoss->user_id);
                    $lossUser->balance += $biddingLoss->amount;
                    $lossUser->save();

                    $transaction               = new Transaction();
                    $transaction->user_id      = $lossUser->id;
                    $transaction->amount       = $biddingLoss->amount;
                    $transaction->post_balance = $lossUser->balance;
                    $transaction->charge       = 0;
                    $transaction->trx_type     = '+';
                    $transaction->details      = 'Refund for unsuccessful bids ' . gs('cur_sym') . showAmount($biddingLoss->amount);
                    $transaction->trx          = getTrx();
                    $transaction->remark       = 'bid_refund';
                    $transaction->save();

                    if (userNotifyPermission($lossUser, 'refund')) {
                        notify($biddingLoss->user, 'BID_REFUND', [
                            'title'         => $accountListing->title,
                            'refund_amount' => showAmount($biddingLoss->amount,currencyFormat:false),
                            'pricing_model' => $accountListing->pricing_model == Status::AUCTION ? 'Auction' : 'Fixed',
                        ]);
                    }
                }
            }
        } catch (\Throwable $e) {
            // Ignore if auction table structure varies
        }
    }

    /**
     * Cron Job: Check Cookie Health for 1 account per scheduled run
     * Rate-limited to check 1 account per run to avoid Google / platform IP bans.
     */
    public function cookieCheck()
    {
        // Fetch ALL active accounts across active platforms to check whenever the cron schedule triggers
        $accounts = AccountListing::where('status', Status::LISTING_ACTIVE)
            ->whereHas('socialMedia', function ($q) {
                $q->active();
            })
            ->with('socialMedia')
            ->get();

        if ($accounts->isEmpty()) {
            if (request()->target == 'all' || request()->alias || request()->ajax()) {
                $notify[] = ['info', 'All accounts checked within the last 60 seconds.'];
                return back()->withNotify($notify);
            }
            return response()->json(['success' => true, 'message' => 'All accounts checked within the last 60 seconds.']);
        }

        $checkedCount = 0;
        $platformsToRebalance = [];
        $expiredAccountsNotified = [];

        foreach ($accounts as $acc) {
            // If the account is actively managed via Admin Extension live sync, SKIP cron cURL verification completely!
            // Fresh cookies are delivered directly by the admin's trusted browser, so no server cURL checking is needed.
            if ($acc->last_sync_source === 'admin_extension' || !empty($acc->last_synced_at)) {
                $acc->cookie_status = 1;
                $acc->cookie_check_error = null;
                $acc->save();
                $checkedCount++;
                continue;
            }

            $prevStatus = $acc->cookie_status;
            $result = $this->verifyAccountCookieHealth($acc);

            // Handle network timeouts gracefully without flipping cookie status falsely
            if (!$result['valid'] && !empty($result['is_network_error'])) {
                $acc->cookie_check_error = $result['error'];
                $acc->cookie_checked_at = now();
                $acc->save();
                $checkedCount++;
                continue;
            }

            $newStatus = $result['valid'] ? 1 : 0;

            $acc->cookie_status = $newStatus;
            $acc->cookie_check_error = $result['error'] ?: null;
            $acc->cookie_checked_at = now();

            if ($result['valid'] && !empty($result['account_name'])) {
                $dupMatch = \App\Http\Controllers\Admin\AccountListingController::checkDuplicateName($result['account_name'], $acc->social_media_id, $acc->id);
                if ($dupMatch) {
                    $acc->cookie_status = 0;
                    $acc->cookie_check_error = "Duplicate account: Verified name '{$dupMatch->title}' belongs to account ID {$dupMatch->id}.";
                    $acc->save();
                    $checkedCount++;
                    continue;
                }
                $acc->title = $result['account_name'];
            }

            $acc->save();

            if ($prevStatus !== $newStatus) {
                $platformsToRebalance[$acc->social_media_id] = true;
            }

            if ($newStatus === 0 && $prevStatus !== 0) {
                $expiredAccountsNotified[] = [
                    'account' => $acc,
                    'error' => $result['error'] ?: 'Cookie verification failed'
                ];
            }

            $checkedCount++;
        }

        // Use 'keep_manual' mode so users already on a working valid account are KEPT on their current account!
        // This prevents logging active users out during background cron checks!
        foreach (array_keys($platformsToRebalance) as $pId) {
            \App\Http\Controllers\Admin\SocialMediaController::executeLoadBalance($pId, 'keep_manual');
        }

        foreach ($expiredAccountsNotified as $item) {
            \App\Lib\WhatsappNotification::sendCookieExpiryNotification($item['account'], $item['error']);
        }

        $msg = "Checked cookies for {$checkedCount} active account(s).";

        if (request()->target == 'all' || request()->alias || request()->ajax()) {
            $notify[] = ['success', $msg];
            return back()->withNotify($notify);
        }

        return response()->json([
            'success' => true,
            'message' => $msg,
            'checked_count' => $checkedCount
        ]);
    }

    /**
     * Helper to verify cookie health for an AccountListing
     * Performs a 100% live HTTP scan request without relying on local expiration timestamps.
     */
    public function verifyAccountCookieHealth($account)
    {
        $rawInfo = $account->account_info;
        if (is_string($rawInfo)) {
            $rawInfo = json_decode($rawInfo, true);
        }

        if (empty($rawInfo)) {
            return ['valid' => false, 'error' => 'No cookie data configured'];
        }

        $platformName = strtolower($account->socialMedia->name ?? '');
        $accountTitle = strtolower($account->title ?? '');
        $isGoogleFlow = str_contains($platformName, 'google') || str_contains($accountTitle, 'flow');

        // Convert array/object of cookies into standard header string
        // We DO NOT check local expiration dates; we scan live.
        $cookieHeaderParts = [];

        if (is_array($rawInfo)) {
            foreach ($rawInfo as $item) {
                $item = (array) $item;
                $name = $item['name'] ?? $item['key'] ?? null;
                $val  = $item['value'] ?? $item['val'] ?? null;
                $domain = strtolower($item['domain'] ?? '');

                if ($name && $val !== null) {
                    if ($isGoogleFlow) {
                        // Avoid HTTP 431 Request Header Fields Too Large and CookieMismatch errors
                        // Only include cookies meant for Google Flow / Google Root Auth
                        $isRelevantDomain = empty($domain) 
                            || $domain === '.google.com' 
                            || $domain === 'google.com' 
                            || str_contains($domain, 'flow.google.com') 
                            || str_contains($domain, 'labs.google')
                            || str_contains($domain, 'accounts.google.com');

                        if (!$isRelevantDomain) {
                            continue; // Skip adsense, docs, mail, wallet, etc.
                        }

                        // Skip bulky analytics trackers
                        if (str_starts_with($name, '_ga') || str_starts_with($name, '__utm') || $name === 'NID') {
                            continue;
                        }
                    }

                    $cookieHeaderParts[] = "$name=$val";
                }
            }
        }

        if (empty($cookieHeaderParts)) {
            return ['valid' => false, 'error' => 'Invalid cookie structure'];
        }

        $cookieHeaderString = implode('; ', $cookieHeaderParts);



        if ($isGoogleFlow) {
            // Direct inspection of flow.google.com homepage (new official URL)
            $ch = curl_init('https://flow.google.com/');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
            curl_setopt($ch, CURLOPT_TIMEOUT, 12);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 6);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Cookie: ' . $cookieHeaderString,
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
                'Accept-Language: en-US,en;q=0.9',
            ]);

            $htmlResp = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $effectiveUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
            $curlErr = curl_error($ch);
            curl_close($ch);

            if ($curlErr) {
                return ['valid' => false, 'error' => 'Network error connecting to Google Flow: ' . $curlErr, 'is_network_error' => true];
            }

            // Redirected to login or unauthenticated landing
            if (str_contains($effectiveUrl, 'accounts.google.com') || str_contains($effectiveUrl, 'ServiceLogin') || str_contains($effectiveUrl, 'signin')) {
                return ['valid' => false, 'error' => 'Session expired (Redirected to Google Login)'];
            }

            if (str_contains($effectiveUrl, 'flow.google.com/about') && !str_contains($effectiveUrl, 'pli=1')) {
                return ['valid' => false, 'error' => 'Session expired (Redirected to Flow About page)'];
            }

            // If page contains flow app config or AiSandboxAngularFrontend
            $isFlowApp = str_contains($htmlResp, 'AiSandboxAngularFrontend') || str_contains($htmlResp, 'ppConfig') || str_contains($htmlResp, 'Google Flow');

            // Look for user email in page response
            $extractedName = null;
            if (preg_match('/([a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+)/', $htmlResp, $emailMatch)) {
                $userEmail = trim($emailMatch[1]);
                if (!str_ends_with($userEmail, '@google.com') || str_contains($htmlResp, '"oPEP7c"')) {
                    $extractedName = $userEmail;
                }
            }

            if ($isFlowApp && $httpCode === 200) {
                return ['valid' => true, 'error' => null, 'account_name' => $extractedName ?: $account->title];
            }


            // Optional fallback: Legacy NextAuth session check (for accounts still on labs.google)
            $chLegacy = curl_init('https://labs.google/fx/api/auth/session');
            curl_setopt($chLegacy, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($chLegacy, CURLOPT_TIMEOUT, 6);
            curl_setopt($chLegacy, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($chLegacy, CURLOPT_HTTPHEADER, [
                'Cookie: ' . $cookieHeaderString,
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept: application/json, text/html, */*'
            ]);
            $legacyResp = curl_exec($chLegacy);
            curl_close($chLegacy);

            $json = json_decode($legacyResp, true);
            if (is_array($json) && !empty($json['user'])) {
                $name = trim($json['user']['name'] ?? $json['user']['email'] ?? '');
                return ['valid' => true, 'error' => null, 'account_name' => $name];
            }

            return ['valid' => false, 'error' => 'Session expired (Unauthenticated on Google Flow)'];
        }

        $targetUrl = $account->socialMedia->url ?? 'https://flow.google.com/';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $targetUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 12);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 6);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Cookie: ' . $cookieHeaderString,
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Accept: application/json, text/html, */*',
            'Accept-Language: en-US,en;q=0.9',
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $effectiveUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        $curlErr = curl_error($ch);
        curl_close($ch);

        if ($curlErr) {
            return [
                'valid' => false,
                'error' => 'Network error: ' . $curlErr,
                'is_network_error' => true
            ];
        }


        // For other platforms, check HTTP status & redirect URL
        if (str_contains($effectiveUrl, 'accounts.google.com') || str_contains($effectiveUrl, 'ServiceLogin') || str_contains($effectiveUrl, 'signin') || str_contains($effectiveUrl, 'login')) {
            return ['valid' => false, 'error' => 'Session expired (Redirected to login page)'];
        }

        if (str_contains($response, 'Sign in') || str_contains($response, 'ServiceLogin') || str_contains($response, 'identifierInterface')) {
            return ['valid' => false, 'error' => 'Session expired (Login form detected)'];
        }

        if ($httpCode >= 400) {
            return ['valid' => false, 'error' => "HTTP Error Code $httpCode"];
        }

        $extractedName = null;
        $json = json_decode($response, true);
        if (is_array($json)) {
            $extractedName = $json['user']['name'] 
                ?? $json['user']['email'] 
                ?? $json['name'] 
                ?? $json['email'] 
                ?? $json['username'] 
                ?? null;
        }

        return ['valid' => true, 'error' => null, 'account_name' => is_string($extractedName) ? trim($extractedName) : null];
    }

    public static function getAutoBuyState()
    {
        $state = \Illuminate\Support\Facades\Cache::get('warzone_gemini_autobuy_state');
        if (!$state) {
            $filePath = storage_path('app/warzone_autobuy.json');
            if (file_exists($filePath)) {
                $state = json_decode(@file_get_contents($filePath), true);
            }
        }
        if (!$state) {
            $state = [
                'target_product' => 'Gemini AI Pro 18M (S_01)',
                'is_active'      => true,
                'last_check'     => null,
                'last_status'    => 'Initialized - awaiting next cron cycle',
                'last_stock'     => 0,
                'total_checks'   => 0,
                'total_bought'   => 0,
                'recent_checks'  => [],
                'orders'         => [],
            ];
        }
        return $state;
    }

    public static function recordAutoBuyCheck($entry, $orderData = null)
    {
        $state = self::getAutoBuyState();
        $tz = config('app.timezone', 'Asia/Karachi');
        $state['last_check']   = now()->timezone($tz)->format('Y-m-d h:i:s A');
        $state['last_status']  = $entry['message'] ?? 'Checked';
        $state['last_stock']   = $entry['stock'] ?? 0;
        $state['total_checks'] = ($state['total_checks'] ?? 0) + 1;

        if (!isset($state['recent_checks']) || !is_array($state['recent_checks'])) {
            $state['recent_checks'] = [];
        }
        array_unshift($state['recent_checks'], $entry);
        $state['recent_checks'] = array_slice($state['recent_checks'], 0, 20);

        if ($orderData) {
            if (!isset($state['orders']) || !is_array($state['orders'])) {
                $state['orders'] = [];
            }
            array_unshift($state['orders'], $orderData);
            $state['total_bought'] = ($state['total_bought'] ?? 0) + ($orderData['quantity'] ?? 0);
        }

        \Illuminate\Support\Facades\Cache::put('warzone_gemini_autobuy_state', $state, now()->addDays(30));
        @file_put_contents(storage_path('app/warzone_autobuy.json'), json_encode($state, JSON_PRETTY_PRINT));

        return $state;
    }

    /**
     * Auto-buy Gemini AI Pro 18M from Warzone API as soon as stock is available.
     * Buys the maximum affordable quantity with the current wallet balance.
     */
    public function warzoneAutoBuyGemini()
    {
        $apiKey  = 'WAR_LoV98CIYjX6S6N17Hvmc2c2K';
        $baseUrl = 'https://api.warzoneshop.in/api/v1';

        try {
            // 1. Fetch current wallet balance
            $accountRes = \Illuminate\Support\Facades\Http::withHeaders([
                'X-API-Key' => $apiKey,
                'Accept'    => 'application/json',
            ])->timeout(12)->get("$baseUrl/me")->json();

            $balance = floatval($accountRes['wallet_balance'] ?? 0);
            if ($balance <= 0) {
                self::recordAutoBuyCheck([
                    'time'    => now()->timezone(config('app.timezone', 'Asia/Karachi'))->format('h:i:s A'),
                    'date'    => now()->format('Y-m-d'),
                    'stock'   => 0,
                    'balance' => 0,
                    'status'  => 'insufficient_balance',
                    'message' => 'Wallet balance is $0.00. Please top up.',
                ]);
                return response()->json([
                    'status'     => 'insufficient_balance',
                    'message'    => 'Wallet balance is $0.00. Please top up your wallet first.',
                    'balance'    => 0,
                    'checked_at' => now()->timezone(config('app.timezone', 'Asia/Karachi'))->format('Y-m-d h:i:s A'),
                ]);
            }

            // 2. Fetch products and locate Gemini Pro
            $productsRes = \Illuminate\Support\Facades\Http::withHeaders([
                'X-API-Key' => $apiKey,
                'Accept'    => 'application/json',
            ])->timeout(12)->get("$baseUrl/products")->json();

            $gemini = null;
            foreach ($productsRes['services'] ?? [] as $service) {
                if (($service['service_id'] ?? '') === 'S_01' || stripos($service['name'] ?? '', 'Gemini') !== false) {
                    $gemini = $service;
                    break;
                }
            }

            if (!$gemini) {
                self::recordAutoBuyCheck([
                    'time'    => now()->timezone(config('app.timezone', 'Asia/Karachi'))->format('h:i:s A'),
                    'date'    => now()->format('Y-m-d'),
                    'stock'   => 0,
                    'balance' => $balance,
                    'status'  => 'error',
                    'message' => 'Gemini AI Pro 18M product not found in Warzone API services list.',
                ]);
                return response()->json([
                    'status'     => 'error',
                    'message'    => 'Gemini AI Pro 18M product not found in Warzone API services list.',
                    'checked_at' => now()->timezone(config('app.timezone', 'Asia/Karachi'))->format('Y-m-d h:i:s A'),
                ], 404);
            }

            $serviceId = $gemini['service_id'] ?? 'S_01';
            $stock     = intval($gemini['stock'] ?? 0);
            $orderable = !empty($gemini['orderable']);

            // 3. Check stock availability
            if ($stock <= 0 || !$orderable) {
                self::recordAutoBuyCheck([
                    'time'    => now()->timezone(config('app.timezone', 'Asia/Karachi'))->format('h:i:s A'),
                    'date'    => now()->format('Y-m-d'),
                    'stock'   => $stock,
                    'balance' => $balance,
                    'status'  => 'waiting',
                    'message' => "Out of Stock (Stock: {$stock}). Standing by for stock...",
                ]);
                return response()->json([
                    'status'     => 'waiting',
                    'message'    => "Gemini AI Pro 18M is currently Out of Stock (Stock: {$stock}). Standing by for stock...",
                    'stock'      => $stock,
                    'balance'    => $balance,
                    'orderable'  => $orderable,
                    'checked_at' => now()->timezone(config('app.timezone', 'Asia/Karachi'))->format('Y-m-d h:i:s A'),
                ]);
            }

            // 4. Calculate maximum affordable quantity
            $basePrice  = floatval($gemini['price'] ?? 0.55);
            $priceTiers = $gemini['price_tiers'] ?? [];
            $maxToBuy   = 0;

            for ($q = $stock; $q >= 1; $q--) {
                $unitPrice = $basePrice;
                foreach ($priceTiers as $tier) {
                    if ($q >= $tier['min_qty'] && $q <= $tier['max_qty']) {
                        $unitPrice = floatval($tier['unit_price']);
                        break;
                    }
                }
                $totalCost = $q * $unitPrice;
                if ($totalCost <= $balance) {
                    $maxToBuy = $q;
                    break;
                }
            }

            if ($maxToBuy < 1) {
                self::recordAutoBuyCheck([
                    'time'    => now()->timezone(config('app.timezone', 'Asia/Karachi'))->format('h:i:s A'),
                    'date'    => now()->format('Y-m-d'),
                    'stock'   => $stock,
                    'balance' => $balance,
                    'status'  => 'insufficient_funds',
                    'message' => "Stock available ({$stock}), but balance (\${$balance}) < unit price (\${$basePrice}).",
                ]);
                return response()->json([
                    'status'     => 'insufficient_funds',
                    'message'    => "Stock is available ({$stock}), but current balance (\${$balance}) is less than unit price (\${$basePrice}).",
                    'stock'      => $stock,
                    'balance'    => $balance,
                    'checked_at' => now()->timezone(config('app.timezone', 'Asia/Karachi'))->format('Y-m-d h:i:s A'),
                ]);
            }

            // 5. Instantly place order
            $orderPayload = [
                'service_id' => $serviceId,
                'quantity'   => $maxToBuy,
            ];

            $orderRes = \Illuminate\Support\Facades\Http::withHeaders([
                'X-API-Key' => $apiKey,
                'Accept'    => 'application/json',
            ])->timeout(20)->post("$baseUrl/order", $orderPayload)->json();

            \Illuminate\Support\Facades\Log::info("Warzone Auto Buy Gemini Triggered: Qty: {$maxToBuy}, Balance: \${$balance}, Response: " . json_encode($orderRes));

            // Save delivered products / links to WarzonePurchasedLink database table
            $products = $orderRes['products'] ?? ($orderRes['delivered_products'] ?? []);
            if (!empty($products) && is_array($products)) {
                foreach ($products as $delivItem) {
                    try {
                        \App\Models\WarzonePurchasedLink::firstOrCreate(
                            ['link' => trim($delivItem)],
                            [
                                'product_name' => $gemini['name'] ?? 'Gemini AI Pro 18M',
                                'service_id'   => $serviceId,
                                'order_id'     => $orderRes['order_id'] ?? null,
                                'source'       => 'bot',
                                'status'       => \App\Models\WarzonePurchasedLink::STATUS_AVAILABLE,
                                'purchased_at' => now(),
                            ]
                        );
                    } catch (\Exception $linkEx) {
                        \Illuminate\Support\Facades\Log::error("Failed to store purchased link: " . $linkEx->getMessage());
                    }
                }
            }


            self::recordAutoBuyCheck([
                'time'    => now()->timezone(config('app.timezone', 'Asia/Karachi'))->format('h:i:s A'),
                'date'    => now()->format('Y-m-d'),
                'stock'   => $stock,
                'balance' => $balance,
                'status'  => 'ordered',
                'message' => "SUCCESS: Placed order for {$maxToBuy} Gemini Pro accounts!",
            ], [
                'time'             => now()->timezone(config('app.timezone', 'Asia/Karachi'))->format('Y-m-d h:i:s A'),
                'quantity'         => $maxToBuy,
                'service_id'       => $serviceId,
                'order_result'     => $orderRes,
            ]);


            return response()->json([
                'status'         => 'ordered',
                'message'        => "Successfully placed instant auto-buy order for {$maxToBuy} Gemini Pro account(s)!",
                'quantity_bought'=> $maxToBuy,
                'balance_before' => $balance,
                'order_result'   => $orderRes,
                'executed_at'    => now()->timezone(config('app.timezone', 'Asia/Karachi'))->format('Y-m-d h:i:s A'),
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Warzone Auto Buy Gemini Exception: " . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Exception occurred during auto-buy check: ' . $e->getMessage(),
            ], 500);
        }
    }
}

