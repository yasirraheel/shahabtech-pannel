<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Status;
use Illuminate\Http\Request;
use App\Models\AccountListing;
use App\Http\Controllers\Controller;
use App\Models\SocialMedia;
use App\Models\Plan;
use App\Models\User;

class AccountListingController extends Controller
{
    // All accounts across all platforms
    public function index(Request $request)
    {
        $pageTitle = 'All Accounts';

        // Reset persistent filter if requested
        if ($request->has('reset_filter')) {
            session()->forget('admin_account_filter_platforms');
            return redirect()->route('admin.account.listing.index');
        }

        // Update persistent filter if submitted
        if ($request->has('platforms') || $request->has('filter_applied')) {
            $platforms = $request->input('platforms', []);
            if (!is_array($platforms)) {
                $platforms = array_filter([$platforms]);
            }
            $platforms = array_map('intval', array_filter($platforms));
            session(['admin_account_filter_platforms' => $platforms]);
        }

        $selectedPlatforms = session('admin_account_filter_platforms', []);

        if ($request->has('sort')) {
            $sortVal = in_array($request->sort, ['last_updated', 'created_at', 'title_asc', 'cookie_health']) ? $request->sort : 'last_updated';
            session(['admin_account_sort' => $sortVal]);
        }

        $currentSort = session('admin_account_sort', 'last_updated');

        $query = AccountListing::searchable(['title'])
            ->with('socialMedia', 'plan', 'category');

        if (!empty($selectedPlatforms)) {
            $query->whereIn('social_media_id', $selectedPlatforms);
        }

        if ($currentSort === 'last_updated') {
            $query->orderBy('updated_at', 'desc');
        } elseif ($currentSort === 'cookie_health') {
            $query->orderByRaw('cookie_status IS NULL ASC, cookie_status ASC, updated_at DESC');
        } elseif ($currentSort === 'title_asc') {
            $query->orderBy('title', 'asc');
        } else {
            $query->orderBy('id', 'desc');
        }

        $accountListings = $query->paginate(getPaginate());

        $plans = Plan::active()->get();
        $socialMedias = SocialMedia::active()->get();
        $categories = \App\Models\Category::active()->get();

        return view('admin.account_listing.index', compact('pageTitle', 'accountListings', 'plans', 'socialMedias', 'categories', 'selectedPlatforms'));
    }

    // Accounts for a specific platform
    public function byPlatform(Request $request, $platformId)
    {
        $platform  = SocialMedia::findOrFail($platformId);
        $pageTitle = 'Accounts: ' . $platform->name;

        if ($request->has('sort')) {
            $sortVal = in_array($request->sort, ['last_updated', 'created_at', 'title_asc', 'cookie_health']) ? $request->sort : 'last_updated';
            session(['admin_account_sort' => $sortVal]);
        }

        $currentSort = session('admin_account_sort', 'last_updated');

        $query = AccountListing::where('social_media_id', $platformId)
            ->searchable(['title'])
            ->with('plan');

        if ($currentSort === 'last_updated') {
            $query->orderBy('updated_at', 'desc');
        } elseif ($currentSort === 'cookie_health') {
            $query->orderByRaw('cookie_status IS NULL ASC, cookie_status ASC, updated_at DESC');
        } elseif ($currentSort === 'title_asc') {
            $query->orderBy('title', 'asc');
        } else {
            $query->orderBy('id', 'desc');
        }

        $accountListings = $query->paginate(getPaginate());
        $plans = Plan::active()->get();
        $categories = \App\Models\Category::active()->get();
        return view('admin.account_listing.by_platform', compact('pageTitle', 'accountListings', 'platform', 'plans', 'categories'));
    }

    public static function getCookieFingerprint($cookieInput)
    {
        if (empty($cookieInput)) {
            return '';
        }

        $decoded = is_string($cookieInput) ? json_decode($cookieInput, true) : $cookieInput;
        $pairs = [];

        if (is_array($decoded)) {
            foreach ($decoded as $item) {
                if (is_array($item) || is_object($item)) {
                    $item = (array) $item;
                    $name = $item['name'] ?? $item['key'] ?? null;
                    $val  = $item['value'] ?? $item['val'] ?? null;
                    if ($name && $val !== null) {
                        $pairs[$name] = $val;
                    }
                }
            }
        } elseif (is_string($cookieInput)) {
            $parts = explode(';', $cookieInput);
            foreach ($parts as $part) {
                if (str_contains($part, '=')) {
                    list($k, $v) = explode('=', trim($part), 2);
                    if ($k && $v !== null) {
                        $pairs[trim($k)] = trim($v);
                    }
                }
            }
        }

        if (empty($pairs)) {
            return '';
        }

        ksort($pairs);

        $normalizedParts = [];
        foreach ($pairs as $k => $v) {
            $normalizedParts[] = "$k=$v";
        }

        return hash('sha256', implode('&', $normalizedParts));
    }

    public static function formatUniqueAccountTitle($title, $accountId)
    {
        if (empty($title)) {
            return 'Account #' . $accountId;
        }

        $cleanTitle = preg_replace('/\s*#\d+$/', '', trim($title));

        $otherCount = AccountListing::where('id', '!=', $accountId)
            ->where('title', 'LIKE', $cleanTitle . '%')
            ->count();

        if ($otherCount > 0) {
            $matchingIds = AccountListing::where('title', 'LIKE', $cleanTitle . '%')
                ->orderBy('id', 'asc')
                ->pluck('id')
                ->toArray();

            $pos = array_search($accountId, $matchingIds);
            if ($pos !== false && $pos > 0) {
                return $cleanTitle . ' #' . ($pos + 1);
            }
        }

        return $cleanTitle;
    }

    public function store(Request $request, $id = null)
    {
        $request->validate([
            'title'           => 'required',
            'social_media_id' => 'required',
            'category_id'     => 'required',
            'plan_id'         => 'nullable',
            'url'             => 'required',
            'account_info'    => 'required',
            'instructions'    => 'nullable|string',
        ]);

        $newFingerprint = self::getCookieFingerprint($request->account_info);

        if ($newFingerprint) {
            $existingAccounts = AccountListing::when($id, function($q) use ($id) {
                $q->where('id', '!=', $id);
            })->get();

            foreach ($existingAccounts as $existingAcc) {
                $existingFingerprint = self::getCookieFingerprint($existingAcc->account_info);
                if ($existingFingerprint && $existingFingerprint === $newFingerprint) {
                    $notify[] = ['error', "This cookie data is already configured on account '{$existingAcc->title}'! Every account must have unique cookie data."];
                    return back()->withNotify($notify)->withInput();
                }
            }
        }

        if ($id) {
            $account       = AccountListing::findOrFail($id);
            $notifyMessage = 'Account updated successfully';
        } else {
            $account       = new AccountListing();
            $notifyMessage = 'Account added successfully';
        }

        $account->title           = $request->title;
        $account->social_media_id = $request->social_media_id;
        $account->category_id     = $request->category_id;
        $account->plan_id         = $request->plan_id ?: 0;
        $account->url             = $request->url;
        $account->account_info    = json_decode($request->account_info) ? json_decode($request->account_info) : $request->account_info;
        $account->instructions    = $request->instructions;
        $account->status          = Status::LISTING_ACTIVE;
        $account->save();

        // Perform instant live cookie verification & auto-update account title if name extracted
        $cronController = new \App\Http\Controllers\CronController();
        $verifyResult   = $cronController->verifyAccountCookieHealth($account);

        $account->cookie_status      = $verifyResult['valid'] ? 1 : 0;
        $account->cookie_check_error = $verifyResult['error'] ?: null;
        $account->cookie_checked_at  = now();

        if ($verifyResult['valid'] && !empty($verifyResult['account_name'])) {
            $formattedTitle = self::formatUniqueAccountTitle($verifyResult['account_name'], $account->id);
            $account->title = $formattedTitle;
            $notifyMessage .= " (Account title updated to '{$formattedTitle}')";
        }

        $account->save();

        // Trigger manual load balancer logic (override_manual) for active non-expired subscribed users
        SocialMediaController::executeLoadBalance($account->social_media_id, 'override_manual');

        $notify[] = ['success', $notifyMessage];
        return back()->withNotify($notify);
    }

    public function modifyExpiry(Request $request, $id)
    {
        $request->validate([
            'action' => 'required|in:extend,decrease',
        ]);

        $account = AccountListing::findOrFail($id);
        $cookies = $account->account_info;

        if (!$cookies || !is_array($cookies)) {
            $notify[] = ['error', 'No cookies found or invalid format.'];
            return back()->withNotify($notify);
        }

        $days = 30;
        $seconds = $days * 24 * 60 * 60;
        
        $modifiedCount = 0;

        foreach ($cookies as &$cookie) {
            if ($request->action == 'extend') {
                if (isset($cookie->session) && $cookie->session == true) {
                    // Convert session cookie to persistent cookie
                    $cookie->session = false;
                    $cookie->expirationDate = time() + $seconds;
                    $modifiedCount++;
                } else if (isset($cookie->expirationDate)) {
                    $cookie->expirationDate += $seconds;
                    $modifiedCount++;
                }
            } else if ($request->action == 'decrease') {
                if (isset($cookie->expirationDate)) {
                    $cookie->expirationDate -= $seconds;
                    $modifiedCount++;
                }
            }
        }

        $account->account_info = $cookies;
        $account->save();

        $actionText = $request->action == 'extend' ? 'Extended' : 'Decreased';
        $notify[] = ['success', "$actionText expiry for $modifiedCount cookies by $days days."];
        
        return back()->withNotify($notify);
    }

    public function status($id)
    {
        $account = AccountListing::findOrFail($id);

        if ($account->status == Status::LISTING_ACTIVE) {
            $account->status = Status::LISTING_INACTIVE;
        } else {
            $account->status = Status::LISTING_ACTIVE;
        }
        $account->save();

        SocialMediaController::executeLoadBalance($account->social_media_id, 'override_manual');

        $statusText = $account->status == Status::LISTING_ACTIVE ? 'enabled' : 'disabled';
        $notify[] = ['success', "Account {$statusText} successfully."];
        return back()->withNotify($notify);
    }

    public function delete($id)
    {
        $account = AccountListing::findOrFail($id);
        $platformId = $account->social_media_id;
        $account->delete();

        SocialMediaController::executeLoadBalance($platformId, 'override_manual');

        $notify[] = ['success', 'Account deleted successfully. Assigned users updated.'];
        return back()->withNotify($notify);
    }

    public function duplicate(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string',
        ]);

        $original = AccountListing::findOrFail($id);
        $duplicateFingerprint = self::getCookieFingerprint($original->account_info);

        if ($duplicateFingerprint) {
            $notify[] = ['error', "Cannot duplicate account: The cookie data is already assigned to '{$original->title}'. Each account must have unique cookie data."];
            return back()->withNotify($notify)->withInput();
        }

        $duplicate = new AccountListing();
        $duplicate->title           = $request->title;
        $duplicate->social_media_id = $original->social_media_id;
        $duplicate->category_id     = $original->category_id;
        $duplicate->plan_id         = $original->plan_id;
        $duplicate->url             = $original->url;
        $duplicate->account_info    = $original->account_info;
        $duplicate->instructions    = $original->instructions;
        $duplicate->status          = Status::LISTING_ACTIVE;
        $duplicate->save();

        SocialMediaController::executeLoadBalance($duplicate->social_media_id, 'override_manual');

        $notify[] = ['success', 'Account duplicated successfully'];
        return back()->withNotify($notify);
    }

    public function checkCookie($id)
    {
        $account = AccountListing::with('socialMedia')->findOrFail($id);
        $cronController = new \App\Http\Controllers\CronController();
        $result = $cronController->verifyAccountCookieHealth($account);

        $account->cookie_status = $result['valid'] ? 1 : 0;
        $account->cookie_check_error = $result['error'] ?: null;
        $account->cookie_checked_at = now();

        $titleMsg = "";
        if ($result['valid'] && !empty($result['account_name'])) {
            $formattedTitle = self::formatUniqueAccountTitle($result['account_name'], $account->id);
            $account->title = $formattedTitle;
            $titleMsg = " Account title updated to '{$formattedTitle}'.";
        }

        $account->save();

        $reassignedCount = SocialMediaController::executeLoadBalance($account->social_media_id, 'keep_manual');

        if (!$result['valid']) {
            \App\Lib\WhatsappNotification::sendCookieExpiryNotification($account, $result['error'] ?: 'Cookie verification failed');
        }

        $reassignedText = $reassignedCount > 0 ? " ({$reassignedCount} active user(s) load balanced)" : "";

        if ($result['valid']) {
            $notify[] = ['success', "Cookie for '{$account->title}' is valid!{$titleMsg}"];
        } else {
            $notify[] = ['error', "Cookie for '{$account->title}' is invalid: " . $result['error'] . $reassignedText];
        }

        return back()->withNotify($notify);
    }

    public static function rebalanceAffectedUsersForExpiredAccount(AccountListing $expiredAccount)
    {
        return SocialMediaController::executeLoadBalance($expiredAccount->social_media_id, 'override_manual');
    }
}
