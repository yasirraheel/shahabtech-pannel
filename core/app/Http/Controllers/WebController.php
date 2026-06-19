<?php

namespace App\Http\Controllers;

use App\Models\AccountListing;
use App\Models\BiddingListing;

class WebController extends Controller
{
    public function accountListing()
    {
        $pageTitle       = 'Account Listings';
        $accountListings = AccountListing::active()->activeSocialMedia()->activeCategory()->with('socialMedia')->get();
        return view('Template::account_listings', compact('pageTitle', 'accountListings'));
    }

    public function accountListingDetails($slug, $id)
    {
        $pageTitle      = 'Account Listing Details';
        $accountListing = AccountListing::active()->activeSocialMedia()->activeCategory()->checkPreviousDate()->withMax('accountBidding', 'amount')->findOrFail($id);
        $biddings       = BiddingListing::with('user')->where('account_listing_id', $accountListing->id)->orderBy('amount', 'desc')->get();
        $myBid          = BiddingListing::where('user_id', auth()->id())->where('account_listing_id', $accountListing->id)->first();
        
        $relatedAccounts = AccountListing::active()
            ->withCount('accountBidding')
            ->withMax('accountBidding', 'amount')
            ->where('id', '!=', $accountListing->id)
            ->myBidCount()
            ->MyBid()
            ->checkPreviousDate()
            ->where('category_id', $accountListing->category_id)
            ->get();

        $seoContents['keywords']           = [@$accountListing->category->name, @$accountListing->socialMedia->name];
        $seoContents['social_title']       = $accountListing->title;
        $seoContents['description']        = strLimit(strip_tags($accountListing->description), 150);
        $seoContents['social_description'] = strLimit(strip_tags($accountListing->description), 150);
        $seoContents['image']              = getImage(getFilePath('account_listing_thumb') . '/' . $accountListing->thumbnail_image, '470x275');
        $seoContents['image_size']         = '470x275';

        return view('Template::account_listings_details', compact('pageTitle', 'accountListing', 'biddings', 'relatedAccounts', 'seoContents', 'myBid'));
    }
}
