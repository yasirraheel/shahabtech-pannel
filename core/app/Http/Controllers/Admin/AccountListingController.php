<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Status;
use Illuminate\Http\Request;
use App\Models\AccountListing;
use App\Http\Controllers\Controller;
use App\Models\SocialMedia;
use App\Models\Plan;

class AccountListingController extends Controller
{
    // All accounts across all platforms
    public function index(Request $request)
    {
        $pageTitle       = 'All Accounts';
        $accountListings = AccountListing::searchable(['title'])
            ->with('socialMedia', 'plan')
            ->latest()
            ->paginate(getPaginate());
        $plans = Plan::active()->get();
        $socialMedias = SocialMedia::active()->get();
        $categories = \App\Models\Category::active()->get();
        return view('admin.account_listing.index', compact('pageTitle', 'accountListings', 'plans', 'socialMedias', 'categories'));
    }

    // Accounts for a specific platform
    public function byPlatform(Request $request, $platformId)
    {
        $platform        = SocialMedia::findOrFail($platformId);
        $pageTitle       = 'Accounts: ' . $platform->name;
        $accountListings = AccountListing::where('social_media_id', $platformId)
            ->searchable(['title'])
            ->with('plan')
            ->latest()
            ->paginate(getPaginate());
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
        return AccountListing::changeStatus($id);
    }
}
