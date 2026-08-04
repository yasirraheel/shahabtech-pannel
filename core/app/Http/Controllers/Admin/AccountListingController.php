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
            $sortVal = in_array($request->sort, ['last_updated', 'created_at', 'title_asc']) ? $request->sort : 'last_updated';
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
            $sortVal = in_array($request->sort, ['last_updated', 'created_at', 'title_asc']) ? $request->sort : 'last_updated';
            session(['admin_account_sort' => $sortVal]);
        }

        $currentSort = session('admin_account_sort', 'last_updated');

        $query = AccountListing::where('social_media_id', $platformId)
            ->searchable(['title'])
            ->with('plan');

        if ($currentSort === 'last_updated') {
            $query->orderBy('updated_at', 'desc');
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

        $platformId = $account->social_media_id;
        $allPlatformAccountIds = AccountListing::where('social_media_id', $platformId)->pluck('id')->toArray();
        $activeListings = AccountListing::where('social_media_id', $platformId)
            ->where('status', Status::LISTING_ACTIVE)
            ->get();

        $affectedUsers = User::where(function($q) use ($allPlatformAccountIds) {
            foreach ($allPlatformAccountIds as $accId) {
                $q->orWhereJsonContains('account_ids', (int) $accId)
                  ->orWhereJsonContains('account_ids', (string) $accId);
            }
        })->get();

        foreach ($affectedUsers as $user) {
            $currentAccountIds = (array) ($user->account_ids ?? []);
            $otherAccountIds = array_diff($currentAccountIds, $allPlatformAccountIds);

            if ($activeListings->isNotEmpty()) {
                $bestListing = null;
                $minUserCount = PHP_INT_MAX;

                foreach ($activeListings as $listing) {
                    $count = User::whereJsonContains('account_ids', (int) $listing->id)
                        ->orWhereJsonContains('account_ids', (string) $listing->id)
                        ->count();

                    if ($count < $minUserCount) {
                        $minUserCount = $count;
                        $bestListing = $listing;
                    }
                }

                if ($bestListing) {
                    $otherAccountIds[] = $bestListing->id;
                }
            }

            $user->account_ids = array_values(array_unique($otherAccountIds));
            $user->timestamps = false;
            $user->save();
            $user->timestamps = true;
        }

        $statusText = $account->status == Status::LISTING_ACTIVE ? 'enabled' : 'disabled';
        $notify[] = ['success', "Account {$statusText} successfully. Assigned users re-balanced."];
        return back()->withNotify($notify);
    }

    public function delete($id)
    {
        $account = AccountListing::findOrFail($id);

        $accId = $account->id;
        $affectedUsers = User::whereJsonContains('account_ids', (int) $accId)
            ->orWhereJsonContains('account_ids', (string) $accId)
            ->get();

        foreach ($affectedUsers as $user) {
            $currentAccountIds = (array) ($user->account_ids ?? []);
            $updatedAccountIds = array_diff($currentAccountIds, [(int) $accId, (string) $accId]);
            $user->account_ids = array_values($updatedAccountIds);
            $user->timestamps = false;
            $user->save();
            $user->timestamps = true;
        }

        $account->delete();

        $notify[] = ['success', 'Account deleted successfully. Assigned users updated.'];
        return back()->withNotify($notify);
    }

    public function duplicate(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string',
        ]);

        $original = AccountListing::findOrFail($id);

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

        $notify[] = ['success', 'Account duplicated successfully'];
        return back()->withNotify($notify);
    }
}
