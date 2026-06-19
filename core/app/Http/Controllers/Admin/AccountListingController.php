<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Status;
use Illuminate\Http\Request;
use App\Models\AccountListing;
use App\Models\BiddingListing;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\ListingReport;
use App\Models\SocialMedia;

class AccountListingController extends Controller
{
    public function index(Request $request)
    {
        $pageTitle       = 'All Account Listings';
        $accountListings = $this->listingData($scope = null, $request);
        $categories      = Category::get();
        $socialMedias    = SocialMedia::get();
        return view('admin.account_listing.index', compact('pageTitle', 'accountListings', 'categories', 'socialMedias'));
    }

    public function pending(Request $request)
    {
        $pageTitle  = 'Pending Listings';
        $accountListings = $this->listingData($scope = 'pending', $request);
        $categories = Category::get();
        $socialMedias = SocialMedia::get();
        return view('admin.account_listing.index', compact('pageTitle', 'accountListings','categories','socialMedias'));
    }

    public function active(Request $request)
    {
        $pageTitle  = 'Active Listings';
        $accountListings = $this->listingData($scope = 'active', $request);
        $categories = Category::get();
        $socialMedias = SocialMedia::get();
        return view('admin.account_listing.index', compact('pageTitle', 'accountListings','categories','socialMedias'));
    }

    public function inactive(Request $request)
    {
        $pageTitle  = 'Inactive Listings';
        $accountListings = $this->listingData($scope = 'inactive', $request);
        $categories = Category::get();
        $socialMedias = SocialMedia::get();
        return view('admin.account_listing.index', compact('pageTitle', 'accountListings','categories','socialMedias'));
    }
    public function rejected(Request $request)
    {
        $pageTitle  = 'Rejected Listings';
        $accountListings = $this->listingData($scope = 'rejected', $request);
        $categories = Category::get();
        $socialMedias = SocialMedia::get();
        return view('admin.account_listing.index', compact('pageTitle', 'accountListings','categories','socialMedias'));
    }

    public function draft(Request $request)
    {
        $pageTitle  = 'Draft Listings';
        $accountListings = $this->listingData($scope = 'draft', $request);
        $categories = Category::get();
        $socialMedias = SocialMedia::get();
        return view('admin.account_listing.index', compact('pageTitle', 'accountListings','categories','socialMedias'));
    }
    public function sold(Request $request)
    {
        $pageTitle  = 'Sold Listings';
        $accountListings = $this->listingData($scope = 'sold', $request);
        $categories = Category::get();
        $socialMedias = SocialMedia::get();
        return view('admin.account_listing.index', compact('pageTitle', 'accountListings','categories','socialMedias'));
    }

    public function listingData($scope = null, $request)
    {
        if ($scope) {
            $query = AccountListing::$scope()->searchable(['title', 'user:username'])->with('socialMedia', 'category','user')->withCount(['accountBidding','report']);
        } else {
            $query = AccountListing::searchable(['title', 'user:username'])->with('socialMedia', 'category','user')->withCount(['accountBidding','report']);
        }

        if ($request->category) {
            $query = $query->where('category_id', $request->category);
        }
        if ($request->social_media) {
            $query = $query->where('social_media_id', $request->social_media);
        }
        return $query->latest()->paginate(getPaginate());
    }

    public function details($id)
    {
        $pageTitle  = 'Listing Details';
        $accountListing = AccountListing::with('socialMedia', 'category')->findOrFail($id);
        return view('admin.account_listing.details', compact('pageTitle', 'accountListing'));
    }

    public function approveStatus($id)
    {
        $accountListing = AccountListing::findOrFail($id);
        $accountListing->status = Status::LISTING_ACTIVE;
        $accountListing->save();

        if (userNotifyPermission($accountListing->user,'approved')) {
            notify($accountListing->user, 'ACCOUNT_APPROVE', [
                'title' => $accountListing->title,
                'sell_price' => showAmount($accountListing->sell_price,currencyFormat:false),
                'pricing_model' => $accountListing->pricing_model == Status::AUCTION ? 'Auction' : 'Fixed',
            ]);
        }

        $notify[] = ['success', 'Status change successfully'];
        return back()->withNotify($notify);
    }

    public function rejectStatus(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required'
        ]);

        $accountListing = AccountListing::findOrFail($id);
        $accountListing->reason = $request->reason;
        $accountListing->status = Status::LISTING_REJECTED;
        $accountListing->save();

        if (userNotifyPermission($accountListing->user,'rejected')) {
            notify($accountListing->user, 'ACCOUNT_REJECTED', [
                'title' => $accountListing->title,
                'sell_price' => showAmount($accountListing->sell_price,currencyFormat:false),
                'pricing_model' => $accountListing->pricing_model == Status::AUCTION ? 'Auction' : 'Fixed',
                'reject_reason' => $request->reason,
            ]);
        }

        $notify[] = ['success', 'Status change successfully'];
        return back()->withNotify($notify);
    }

    public function bidding($id)
    {
        $accountListing = AccountListing::findOrFail($id);
        $pageTitle = 'Bids for: '. @$accountListing->title;
        $biddingListings = BiddingListing::with('user')->where('account_listing_id',$id)->paginate(getPaginate());
        return view('admin.account_listing.bidding', compact('pageTitle', 'biddingListings'));
    }

    public function report($id)
    {
        $accountListing = AccountListing::findOrFail($id);
        $pageTitle = 'Report for: '. @$accountListing->title;
        $reports = ListingReport::searchable(['user:email','user:username'])->with('user')->where('account_listing_id',$id)->paginate(getPaginate());
        return view('admin.account_listing.report', compact('pageTitle', 'reports'));
    }
}
