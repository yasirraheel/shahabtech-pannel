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
        $account->status          = Status::LISTING_ACTIVE;
        $account->save();

        $notify[] = ['success', $notifyMessage];
        return back()->withNotify($notify);
    }

    public function status($id)
    {
        return AccountListing::changeStatus($id);
    }
}
