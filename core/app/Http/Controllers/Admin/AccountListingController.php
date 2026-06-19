<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Status;
use Illuminate\Http\Request;
use App\Models\AccountListing;
use App\Models\Category;
use App\Models\SocialMedia;
use App\Models\Plan;
use App\Models\User;

class AccountListingController extends Controller
{
    public function index(Request $request)
    {
        $pageTitle       = 'All Accounts';
        $accountListings = AccountListing::searchable(['title'])->with('socialMedia', 'category', 'plan')->latest()->paginate(getPaginate());
        $categories      = Category::get();
        $socialMedias    = SocialMedia::get();
        $plans           = Plan::get();
        return view('admin.account_listing.index', compact('pageTitle', 'accountListings', 'categories', 'socialMedias', 'plans'));
    }

    public function store(Request $request, $id = null)
    {
        $request->validate([
            'title'           => 'required',
            'social_media_id' => 'required',
            'category_id'     => 'required',
            'plan_id'         => 'required',
            'url'             => 'required|url',
        ]);

        if ($id) {
            $account = AccountListing::findOrFail($id);
            $notifyMessage = 'Account updated successfully';
        } else {
            $account = new AccountListing();
            $notifyMessage = 'Account added successfully';
        }

        $account->title           = $request->title;
        $account->social_media_id = $request->social_media_id;
        $account->category_id     = $request->category_id;
        $account->plan_id         = $request->plan_id;
        $account->url             = $request->url;
        $account->account_info    = json_decode($request->account_info) ? json_decode($request->account_info) : $request->account_info;
        $account->status          = Status::LISTING_ACTIVE;
        $account->save();

        $notify[] = ['success', $notifyMessage];
        return back()->withNotify($notify);
    }

    public function details($id)
    {
        $pageTitle  = 'Listing Details';
        $accountListing = AccountListing::with('socialMedia', 'category')->findOrFail($id);
        return view('admin.account_listing.details', compact('pageTitle', 'accountListing'));
    }

    public function status($id)
    {
        return AccountListing::changeStatus($id);
    }
}
