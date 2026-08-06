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
    }

    /**
     * Cron Job: Check Cookie Health for 1 account per scheduled run
     * Rate-limited to check 1 account per run to avoid Google / platform IP bans.
     */
    public function cookieCheck()
    {
        // Fetch all active accounts that haven't been checked in the last 50 seconds (or unchecked)
        $accounts = AccountListing::where('status', Status::LISTING_ACTIVE)
            ->whereHas('socialMedia', function ($q) {
                $q->active();
            })
            ->where(function($q) {
                $q->whereNull('cookie_checked_at')
                  ->orWhere('cookie_checked_at', '<=', now()->subSeconds(50));
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

        foreach ($accounts as $acc) {
            $prevStatus = $acc->cookie_status;
            $result = $this->verifyAccountCookieHealth($acc);
            $newStatus = $result['valid'] ? 1 : 0;

            $acc->cookie_status = $newStatus;
            $acc->cookie_check_error = $result['error'] ?: null;
            $acc->cookie_checked_at = now();
            $acc->save();

            if ($prevStatus !== $newStatus) {
                $platformsToRebalance[$acc->social_media_id] = true;
            }
            $checkedCount++;
        }

        foreach (array_keys($platformsToRebalance) as $pId) {
            \App\Http\Controllers\Admin\SocialMediaController::executeLoadBalance($pId, 'override_manual');
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

        // Convert array/object of cookies into standard header string
        // We DO NOT check local expiration dates; we scan live.
        $cookieHeaderParts = [];

        if (is_array($rawInfo)) {
            foreach ($rawInfo as $item) {
                $item = (array) $item;
                $name = $item['name'] ?? $item['key'] ?? null;
                $val  = $item['value'] ?? $item['val'] ?? null;

                if ($name && $val !== null) {
                    $cookieHeaderParts[] = "$name=$val";
                }
            }
        }

        if (empty($cookieHeaderParts)) {
            return ['valid' => false, 'error' => 'Invalid cookie structure'];
        }

        $cookieHeaderString = implode('; ', $cookieHeaderParts);

        $platformName = strtolower($account->socialMedia->name ?? '');
        $accountTitle = strtolower($account->title ?? '');
        $isGoogleFlow = str_contains($platformName, 'google') || str_contains($accountTitle, 'flow');

        if ($isGoogleFlow) {
            // For Google Flow, test NextAuth session API endpoint live
            $targetUrl = 'https://labs.google/fx/api/auth/session';
        } else {
            $targetUrl = $account->socialMedia->url ?? 'https://labs.google/fx/api/auth/session';
        }

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
            return ['valid' => false, 'error' => 'Network error: ' . $curlErr];
        }

        if ($isGoogleFlow) {
            // Google Flow session API returning {} (empty JSON object or null user) means unauthenticated / expired
            $json = json_decode($response, true);
            if (empty($json) || !isset($json['user']) || empty($json['user'])) {
                return ['valid' => false, 'error' => 'Session expired (Unauthenticated on Google Flow API)'];
            }

            // Inspect the live session expiration timestamp returned by Google NextAuth API
            if (isset($json['expires'])) {
                $sessionExpiryTs = strtotime($json['expires']);
                if ($sessionExpiryTs && $sessionExpiryTs < time()) {
                    return ['valid' => false, 'error' => 'Google Session Expired (' . date('Y-m-d H:i', $sessionExpiryTs) . ' UTC)'];
                }
            }

            return ['valid' => true, 'error' => null];
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

        return ['valid' => true, 'error' => null];
    }
}
