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

    public static function getSessionToken($cookieInput)
    {
        if (empty($cookieInput)) {
            return '';
        }

        $decoded = is_string($cookieInput) ? json_decode($cookieInput, true) : $cookieInput;
        if (!is_array($decoded) && is_object($decoded)) {
            $decoded = (array) $decoded;
        }

        $sessionKeys = [
            '__Secure-next-auth.session-token',
            'next-auth.session-token',
            '__Secure-1PSID',
            '__Secure-3PSID',
            'SID',
            'session_id',
            'sessionid',
            'session_token',
            'auth_token',
            'access_token',
            'c_user',
            'PHPSESSID',
            'JSESSIONID'
        ];

        if (is_array($decoded)) {
            // First pass: search for primary authentication session tokens
            foreach ($sessionKeys as $sKey) {
                foreach ($decoded as $item) {
                    if (is_array($item) || is_object($item)) {
                        $item = (array) $item;
                        $name = $item['name'] ?? $item['key'] ?? null;
                        $val  = $item['value'] ?? $item['val'] ?? null;
                        if ($name && strcasecmp($name, $sKey) === 0 && !empty($val)) {
                            return strtolower($sKey) . ':' . trim($val);
                        }
                    }
                }
            }

            // Second pass: filter out tracking/volatile cookies and return name=value map hash
            $pairs = [];
            $ignoredNames = ['_ga', '_gid', '_gat', '_grecaptcha', 'search_samesite', 'nid', '1p_jar', 'otz', '__host-gaps', '__host-next-auth.csrf-token'];

            foreach ($decoded as $item) {
                if (is_array($item) || is_object($item)) {
                    $item = (array) $item;
                    $name = strtolower(trim($item['name'] ?? $item['key'] ?? ''));
                    $val  = trim($item['value'] ?? $item['val'] ?? '');

                    if (!empty($name) && !empty($val)) {
                        $isIgnored = false;
                        foreach ($ignoredNames as $ign) {
                            if ($name === $ign || str_starts_with($name, '_ga_') || str_starts_with($name, '_gat_')) {
                                $isIgnored = true;
                                break;
                            }
                        }
                        if (!$isIgnored) {
                            $pairs[$name] = $val;
                        }
                    }
                }
            }

            if (!empty($pairs)) {
                ksort($pairs);
                $parts = [];
                foreach ($pairs as $k => $v) {
                    $parts[] = "$k=$v";
                }
                return 'map:' . hash('sha256', implode('&', $parts));
            }
        } elseif (is_string($cookieInput)) {
            $parts = explode(';', $cookieInput);
            $pairs = [];
            foreach ($parts as $part) {
                if (str_contains($part, '=')) {
                    list($k, $v) = explode('=', trim($part), 2);
                    $kLower = strtolower(trim($k));
                    $vClean = trim($v);
                    if ($kLower && $vClean) {
                        foreach ($sessionKeys as $sKey) {
                            if (strcasecmp($kLower, $sKey) === 0) {
                                return strtolower($sKey) . ':' . $vClean;
                            }
                        }
                        $pairs[$kLower] = $vClean;
                    }
                }
            }
            if (!empty($pairs)) {
                ksort($pairs);
                $partsStr = [];
                foreach ($pairs as $k => $v) {
                    $partsStr[] = "$k=$v";
                }
                return 'map:' . hash('sha256', implode('&', $partsStr));
            }
        }

        return '';
    }

    public static function getCookieFingerprint($cookieInput)
    {
        return self::getSessionToken($cookieInput);
    }

    public static function normalizeAccountName($title)
    {
        if (empty($title)) {
            return '';
        }

        $titleStr = trim($title);

        if (preg_match('/[\w\.-]+@[\w\.-]+\.\w+/', $titleStr, $matches)) {
            return strtolower($matches[0]);
        }

        return strtolower(preg_replace('/\s+/', ' ', $titleStr));
    }

    public static function checkDuplicateName($title, $platformId, $excludeAccountId = null)
    {
        if (empty($title)) {
            return null;
        }

        $normTitle = self::normalizeAccountName($title);
        if (empty($normTitle)) {
            return null;
        }

        $existingAccounts = AccountListing::when($excludeAccountId, function($q) use ($excludeAccountId) {
                $q->where('id', '!=', $excludeAccountId);
            })
            ->where('social_media_id', $platformId)
            ->get();

        foreach ($existingAccounts as $existingAcc) {
            $existingNorm = self::normalizeAccountName($existingAcc->title);
            if (!empty($existingNorm) && $existingNorm === $normTitle) {
                return $existingAcc;
            }
        }

        return null;
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

        $dupMatch = self::checkDuplicateName($request->title, $request->social_media_id, $id);
        if ($dupMatch) {
            $notify[] = ['error', "An account with the name/email '{$dupMatch->title}' already exists! Duplicate accounts are not allowed."];
            return back()->withNotify($notify)->withInput();
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

        // Perform instant live cookie verification & extract live account name
        $cronController = new \App\Http\Controllers\CronController();
        $verifyResult   = $cronController->verifyAccountCookieHealth($account);

        if ($verifyResult['valid'] && !empty($verifyResult['account_name'])) {
            $extractedName = $verifyResult['account_name'];
            $dupMatch = self::checkDuplicateName($extractedName, $account->social_media_id, $account->id);

            if ($dupMatch) {
                if (!$id) {
                    $account->delete();
                } else {
                    $account->cookie_status = 0;
                    $account->cookie_check_error = "Duplicate account name: '{$dupMatch->title}' already exists.";
                    $account->save();
                }
                $notify[] = ['error', "Validation failed: An account with the name/email '{$dupMatch->title}' already exists on this platform! Duplicate account blocked."];
                return back()->withNotify($notify)->withInput();
            }

            $account->title = $extractedName;
            $notifyMessage .= " (Verified as '{$extractedName}')";
        }

        $account->cookie_status      = $verifyResult['valid'] ? 1 : 0;
        $account->cookie_check_error = $verifyResult['error'] ?: null;
        $account->cookie_checked_at  = now();
        $account->save();

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

        $dupMatch = self::checkDuplicateName($request->title, $original->social_media_id);
        if ($dupMatch) {
            $notify[] = ['error', "Cannot duplicate: An account with the name/email '{$dupMatch->title}' already exists!"];
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
            $extractedName = $result['account_name'];
            $dupMatch = self::checkDuplicateName($extractedName, $account->social_media_id, $account->id);

            if ($dupMatch) {
                $account->cookie_status = 0;
                $account->cookie_check_error = "Duplicate account: Verified name '{$dupMatch->title}' already exists on account ID {$dupMatch->id}.";
                $account->save();
                $notify[] = ['error', "Cookie check failed: Verified name '{$dupMatch->title}' already exists on account ID {$dupMatch->id}! Duplicate accounts are not allowed."];
                return back()->withNotify($notify);
            }

            $account->title = $extractedName;
            $titleMsg = " Account title updated to '{$extractedName}'.";
        }

        $account->save();

        $reassignedCount = SocialMediaController::executeLoadBalance($account->social_media_id, 'keep_manual');

        if (!$result['valid']) {
            \App\Lib\WhatsappNotification::sendCookieExpiryNotification($account, $result['error'] ?: 'Cookie verification failed');
        }

        $reassignedText = $reassignedCount > 0 ? " ({$reassignedCount} active user(s) load balanced)" : "";

        return back()->withNotify($notify);
    }

    public static function rebalanceAffectedUsersForExpiredAccount(AccountListing $expiredAccount)
    {
        return SocialMediaController::executeLoadBalance($expiredAccount->social_media_id, 'override_manual');
    }
}
