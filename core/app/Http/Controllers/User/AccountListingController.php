<?php

namespace App\Http\Controllers\User;

use Carbon\Carbon;
use App\Models\Form;
use App\Models\Category;
use App\Constants\Status;
use App\Lib\FormProcessor;
use App\Models\SocialMedia;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\AccountListing;
use App\Models\BiddingListing;
use App\Rules\FileTypeValidate;
use App\Models\AccountCredential;
use App\Models\AdminNotification;
use App\Models\AccountListingImage;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class AccountListingController extends Controller
{
    public function index()
    {
        $pageTitle       = 'Account Listing';
        $accountListings = AccountListing::searchable(['title'])
            ->filter(['category_id', 'social_media_id'])
            ->with('socialMedia', 'category')
            ->withCount('accountBidding')
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(getPaginate());

        return view('Template::user.account_listings.index', compact('pageTitle', 'accountListings'));
    }

    public function socialMediaCategory($id = 0, $editTo = false)
    {

        $pageTitle    = 'Sell Your Account';
        $categories   = Category::active()->get();
        $socialsMedia = SocialMedia::active()->get();

        if ($id) {
            $accountListing = AccountListing::where('user_id', auth()->id())->findOrFail($id);
        } else {
            $accountListing = null;
        }

        if ($editTo) {
            if ($accountListing) {

                if ($accountListing->status == Status::LISTING_ACTIVE || $accountListing->status == Status::LISTING_INACTIVE) {
                    return redirect()->route('user.account.listing.bidding.info', $accountListing->id);
                } elseif ($accountListing->status == Status::LISTING_SOLD) {
                    abort(404);
                }

                $step = $accountListing->step;
                if ($step == 1) {
                    return redirect()->route('user.account.listing.bidding.info', $accountListing->id);
                } elseif ($step == 2) {
                    return redirect()->route('user.account.listing.url.description', $accountListing->id);
                } elseif ($step == 3) {
                    return redirect()->route('user.account.listing.account.info', $accountListing->id);
                } elseif ($step == 4) {
                    return redirect()->route('user.account.listing.account.credential', $accountListing->id);
                } elseif ($step == 5) {
                    return redirect()->route('user.account.listing.thumbnail.image', $accountListing->id);
                } else {
                    return redirect()->route('user.account.listing.publish', $accountListing->id);
                }
            }
        }

        return view('Template::user.account_listings.form.social_media_category', compact('pageTitle', 'categories', 'socialsMedia', 'accountListing'));
    }

    public function socialMediaCategoryStore(Request $request, $id = 0)
    {

        $validator = Validator::make($request->all(), [
            'category'     => 'required|integer',
            'social_media' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => $validator->errors()->all(),
            ]);
        }

        $category = Category::active()->where('id', $request->category)->first();

        if (!$category) {
            return response()->json([
                'status' => 'error',
                'message' => "Category not found",
            ]);
        }

        $socialMedia = SocialMedia::active()->where('id', $request->social_media)->first();

        if (!$socialMedia) {
            return response()->json([
                'status' => 'error',
                'message' => "Social media not found",

            ]);
        }

        $user     = auth()->user();
        $isUpdate = false;

        if ($id) {
            $isUpdate = true;
            $accountListing         = AccountListing::where('user_id', $user->id)->where('id', $id)->first();
            if (!$accountListing) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Unauthorized action",

                ]);
            }
        } else {
            $accountListing       = new AccountListing();
            $accountListing->step = 1;
        }

        if ($accountListing->status == Status::LISTING_ACTIVE || $accountListing->status == Status::LISTING_INACTIVE || $accountListing->status == Status::LISTING_SOLD) {
            return response()->json([
                'status' => 'error',
                'message' => "You can't update this account listing"
            ]);
        }

        $accountListing->status          = Status::LISTING_DRAFT;
        $accountListing->user_id         = $user->id;
        $accountListing->category_id     = $request->category;
        $accountListing->social_media_id = $request->social_media;
        $accountListing->save();

        return response()->json([
            'status'       => 'success',
            'redirect_url' => route('user.account.listing.bidding.info', $accountListing->id),
            'is_update'    => $isUpdate,
        ]);
    }


    public function biddingInfo($id)
    {
        $pageTitle      = 'Sell Your Account';
        $accountListing = AccountListing::where('user_id', auth()->user()->id)->findOrFail($id);

        if ($accountListing->step < 1) {
            abort(404);
        }

        return view('Template::user.account_listings.form.bidding_info', compact('pageTitle', 'accountListing'));
    }

    public function biddingInfoStore(Request $request, $id)
    {
        $validation  = Validator::make($request->all(), [
            'pricing_model'    => 'required|in:1,2',
            'min_price'        => $request->pricing_model == 1 ? 'required|numeric|gt:0' : 'nullable',
            'auction_deadline' => $request->pricing_model == 1 ? ['required', 'date_format:Y-m-d', 'after_or_equal:' . now()->format('Y-m-d')] : ['nullable'],
            'sell_price'       => 'required|numeric',
        ]);

        if ($validation->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validation->errors()->all(),
            ]);
        }

        $user           = auth()->user();
        $accountListing = AccountListing::where('id', $id)->where('user_id', $user->id)->first();

        if (!$accountListing) {
            return response()->json([
                'status'  => 'error',
                'message' => "Acounct listing not found",
            ]);
        }

        if ($request->pricing_model == Status::AUCTION) {
            if ($request->sell_price < $request->min_price) {
                return response()->json([
                    'status'  => 'error',
                    'message' => "The sell price must be greater than min price",

                ]);
            }
        }

        $isUpdate = false;

        if ($accountListing->step >= 2) {
            $isUpdate = true;
        }

        if ($accountListing->pricing_model == Status::AUCTION && ($accountListing->status == Status::LISTING_ACTIVE || $accountListing->status == Status::LISTING_INACTIVE)) {

            $bidding = BiddingListing::where('account_listing_id', $accountListing->id)->orderBy('amount', 'desc')->first();
            if (@$bidding) {
                if ($bidding->amount >= $request->sell_price) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => "The sell price must be greater than " . gs('cur_sym') . showAmount($bidding->amount) . " bidding price",
                    ]);
                }
            }
        } else {
            $accountListing->status = Status::LISTING_DRAFT;
        }


        if (!$isUpdate) {
            $accountListing->step = 2;
        }

        if ($isUpdate) {
            if ($request->title) {
                $accountListing->title = $request->title;
            }
            if ($accountListing->status == Status::LISTING_SOLD) {
                return response()->json([
                    'status' => 'error',
                    'message' => "You can't update this account listing",

                ]);
            }
        }

        $accountListing->pricing_model    = $request->pricing_model;
        $accountListing->min_price        = $request->pricing_model == Status::AUCTION ? $request->min_price : 0;
        $accountListing->sell_price       = $request->sell_price;
        $accountListing->auction_deadline = $request->pricing_model == Status::AUCTION ? Carbon::parse($request->auction_deadline)->format('Y-m-d') : null;
        $accountListing->save();

        return response()->json([
            'status'       => 'success',
            'redirect_url' => route('user.account.listing.url.description', $accountListing->id),
            'is_update'    => $isUpdate,
        ]);
    }

    public function urlDescription($id)
    {
        $pageTitle = 'Sell Your Account';
        $accountListing = AccountListing::where('user_id', auth()->user()->id)->findOrFail($id);
        if ($accountListing->step < 2) {
            abort(404);
        }
        return view('Template::user.account_listings.form.url_description', compact('pageTitle', 'accountListing'));
    }

    public function urlDescriptionStore(Request $request, $id)
    {
        $validation  = Validator::make($request->all(), [
            'title'       => 'required|string',
            'url'         => 'required|url',
            'description' => 'required|string',
        ]);

        if ($validation->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validation->errors()->all(),

            ]);
        }

        $user           = auth()->user();
        $accountListing = AccountListing::where('id', $id)->where('user_id', $user->id)->first();

        if (!$accountListing) {
            return response()->json([
                'status' => 'error',
                'message' => "Account Listing not found",

            ]);
        }

        $isUpdate = false;

        if ($accountListing->step >= 3) {
            $isUpdate = true;
            $accountListing->status = Status::LISTING_DRAFT;
        }

        if (!$isUpdate) {
            $accountListing->step = 3;
        }

        if ($isUpdate) {
            if ($accountListing->status == Status::LISTING_ACTIVE || $accountListing->status == Status::LISTING_INACTIVE || $accountListing->status == Status::LISTING_SOLD) {
                return response()->json([
                    'status' => 'error',
                    'message' => "You can't update this account listing",

                ]);
            }
        }

        $accountListing->title       = $request->title;
        $accountListing->url         = $request->url;
        $accountListing->description = $request->description;
        $accountListing->save();

        return response()->json([
            'status' => 'success',
            'redirect_url' => route('user.account.listing.account.info', $accountListing->id),
            'is_update' => $isUpdate,
        ]);
    }


    public function accountInfo($id)
    {
        $pageTitle = 'Sell Your Account';
        $accountListing = AccountListing::with('socialMedia')->where('user_id', auth()->user()->id)->findOrFail($id);
        if ($accountListing->step < 3) {
            abort(404);
        }

        return view('Template::user.account_listings.form.account_info', compact('pageTitle', 'accountListing'));
    }

    public function accountInfoStore(Request $request, $id)
    {
        $validation  = [
            'is_verified' => 'nullable|in:1',
        ];

        $accountListing = AccountListing::find($id);
        $form           = Form::where('act', 'social_media')->where('id', $accountListing->socialMedia->form_id)->first();
        if ($form) {
            $formData       = $form->form_data;
            $formProcessor  = new FormProcessor();
            $validationRule = $formProcessor->valueValidation($formData);
        }

        $validation     = Validator::make($request->all(), array_merge(@$validationRule ?? [], $validation));

        if ($validation->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validation->errors()->all()

            ]);
        }

        $user           = auth()->user();
        $accountListing = $accountListing->where('id', $id)->where('user_id', $user->id)->first();

        if (!$accountListing) {
            return response()->json([
                'status' => 'error',
                'message' => "Account Listing not found",


            ]);
        }

        $isUpdate = false;

        if ($accountListing->step >= 4) {
            $isUpdate               = true;
            $accountListing->status = Status::LISTING_DRAFT;
        }

        if (!$isUpdate) {
            $accountListing->step = 4;
        }

        if ($isUpdate) {
            if ($accountListing->status == Status::LISTING_ACTIVE || $accountListing->status == Status::LISTING_INACTIVE || $accountListing->status == Status::LISTING_SOLD) {
                return response()->json([
                    'status' => 'error',
                    'message' => "You can't update this account listing",

                ]);
            }
        }

        $userData       = @$formProcessor ? @$formProcessor->processFormData($request, $formData) : null;
        $accountListing->account_info = $userData ?? [];
        $accountListing->is_verified = $request->is_verified;
        $accountListing->save();

        return response()->json([
            'status' => 'success',
            'redirect_url' => route('user.account.listing.account.credential', $accountListing->id),
            'is_update' => $isUpdate,
        ]);
    }

    public function accountCredentials($id)
    {
        $pageTitle = 'Sell Your Account';
        $accountListing = AccountListing::where('user_id', auth()->user()->id)->findOrFail($id);
        if ($accountListing->step < 3) {
            abort(404);
        }
        return view('Template::user.account_listings.form.account_credential', compact('pageTitle', 'accountListing'));
    }

    public function accountCredentialsStore(Request $request, $id)
    {

        $validation  = Validator::make($request->all(), [
            'username'      => 'required|string',
            'email'         => 'required|email',
            'password'      => 'required',
            'mobile_number' => 'nullable:regex:/^([0-9]*)$/',
            'others_info'   => 'nullable|string',
        ]);

        if ($validation->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validation->errors()->all(),

            ]);
        }

        $user           = auth()->user();
        $accountListing = AccountListing::where('id', $id)->where('user_id', $user->id)->first();

        if (!$accountListing) {
            return response()->json([
                'status' => 'error',
                'message' => "Account Listing not found",
            ]);
        }

        $isUpdate = false;

        if ($accountListing->step >= 5) {
            $isUpdate               = true;
            $accountListing->status = Status::LISTING_DRAFT;
        }

        if ($isUpdate) {
            if ($accountListing->status == Status::LISTING_ACTIVE || $accountListing->status == Status::LISTING_INACTIVE || $accountListing->status == Status::LISTING_SOLD) {
                return response()->json([
                    'status' => 'error',
                    'message' => "You can't update this account listing",

                ]);
            }
        }

        if (!$isUpdate) {
            $accountListing->step = 5;
            $accountListing->save();
        }

        if ($isUpdate) {
            $accountCredentials = AccountCredential::where('account_listing_id', $id)->first();
        } else {
            $accountCredentials = new AccountCredential();
        }

        $accountCredentials->account_listing_id = $id;
        $accountCredentials->username           = $request->username;
        $accountCredentials->mobile_number      = $request->mobile_number;
        $accountCredentials->email              = $request->email;
        $accountCredentials->password           = $request->password;
        $accountCredentials->others_info        = $request->others_info;
        $accountCredentials->save();

        return response()->json([
            'status'       => 'success',
            'redirect_url' => route('user.account.listing.thumbnail.image', $accountListing->id),
            'is_update'    => $isUpdate,
        ]);
    }



    public function thumbnailImage($id)
    {
        $pageTitle      = 'Sell Your Account';
        $accountListing = AccountListing::where('user_id', auth()->user()->id)->findOrFail($id);

        if ($accountListing->step < 4) {
            abort(404);
        }

        $images = [];
        foreach ($accountListing->images as $key => $image) {
            $img['id']  = $image->id;
            $img['src'] = getImage(getFilePath('account_listing_images') . '/' . $image->name);
            $images[]   = $img;
        }

        return view('Template::user.account_listings.form.thumbnail_Image', compact('pageTitle', 'accountListing', 'images'));
    }

    public function thumbnailImageStore(Request $request, $id)
    {
        $accountListing  = AccountListing::find($id);

        if ($accountListing->step < 6) {
            $imageRules  = 'required';
        } else {
            $imageRules  = 'nullable';
        }
        $validation  = Validator::make($request->all(), [
            'thumbnail_image' => [$imageRules, 'image', new FileTypeValidate(['jpg', 'jpeg', 'png'])],
            'images'          => ['nullable', 'array', 'max:10'],
            'images.*'        => ['nullable', 'image', new FileTypeValidate(['jpg', 'jpeg', 'png'])],
        ]);

        if ($validation->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validation->errors()->all(),

            ]);
        }

        $user = auth()->user();

        $accountListing = $accountListing->where('id', $id)->where('user_id', $user->id)->first();

        if (!$accountListing) {
            return response()->json([
                'status'  => 'error',
                'message' => "Account listing not found",
            ]);
        }

        $isUpdate = false;

        if ($accountListing->step >= 6) {
            $isUpdate               = true;
            $accountListing->status = Status::LISTING_DRAFT;
        }

        if ($isUpdate) {
            if ($accountListing->status == Status::LISTING_ACTIVE || $accountListing->status == Status::LISTING_INACTIVE || $accountListing->status == Status::LISTING_SOLD) {
                return response()->json([
                    'status' => 'error',
                    'message' => "You can't update this account listing",

                ]);
            }
        }

        if (!$isUpdate) {
            $accountListing->step = 6;
        }

        if ($request->hasFile('thumbnail_image')) {

            try {
                $old = $accountListing->thumbnail_image;
                $accountListing->thumbnail_image = fileUploader($request->thumbnail_image, getFilePath('account_listing_thumb'), getFileSize('account_listing_thumb'), $old, getThumbSize('account_listing_thumb'));
            } catch (\Exception $exp) {
                return response()->json([
                    'status' => 'error',
                    'message' => $exp->getMessage(),

                ]);
            }
        }
        $image = $this->insertImages($request, $accountListing, $id);

        if (!$image) {
            return response()->json([
                'status' => 'error',
                'message' => "Couldn\'t upload account listing images",

            ]);
        }

        $accountListing->save();

        return response()->json([
            'status' => 'success',
            'redirect_url' => route('user.account.listing.publish', $accountListing->id),
            'is_update'    => $isUpdate,
        ]);
    }

    public function publish($id)
    {
        $pageTitle      = 'Sell Your Account';
        $accountListing = AccountListing::where('user_id', auth()->user()->id)->findOrFail($id);
        if ($accountListing->step < 6) {
            abort(404);
        }

        if ($accountListing->status == Status::LISTING_DRAFT) {
            $message = trans('Almost Ready for Publishing');
            $button  = trans('Published Now');
        } elseif ($accountListing->status == Status::LISTING_PENDING) {
            $message = trans('Waiting for Approval');
            $button  = '';
        } elseif ($accountListing->status == Status::LISTING_REJECTED) {
            $message = trans('Listing has been Rejected');
            $button  = trans('Re-publish');
        } else {
            $message = trans('Already Published');
            $button  = '';
        }

        return view('Template::user.account_listings.form.publish', compact('pageTitle', 'accountListing', 'message', 'button'));
    }

    public function publishStore($id)
    {
        $user = auth()->user();
        $accountListing = AccountListing::where('id', $id)->where('user_id', $user->id)->first();

        if (!$accountListing) {
            return response()->json([
                'status' => 'error',
                'message' => "Account listing not found",
            ]);
        }

        if ($accountListing->status == Status::LISTING_ACTIVE || $accountListing->status == Status::LISTING_INACTIVE || $accountListing->status == Status::LISTING_SOLD) {
            return response()->json([
                'status' => 'error',
                'message' => "You can't publish this account listing"
            ]);
        }

        $accountListing->status = Status::LISTING_PENDING;
        $accountListing->save();

        $adminNotification = new AdminNotification();
        $adminNotification->user_id = $user->id;
        $adminNotification->title = 'Request to publish an account listing';
        $adminNotification->click_url = urlPath('admin.account.listing.detail', $accountListing->id);
        $adminNotification->save();

        return response()->json([
            'status' => 'success',
            'redirect_url' => route('user.account.listing.index')
        ]);
    }

    protected function insertImages($request, $listing, $id)
    {
        $path = getFilePath('account_listing_images');
        if ($id) {
            $this->removeImages($request, $listing, $path);
        }

        $hasImages = $request->file('images');

        if ($hasImages) {
            $size      = getFileSize('account_listing_images');
            $thumbSize = getThumbSize('account_listing_images');
            $images    = [];

            foreach ($hasImages as $file) {
                try {
                    $name                      = fileUploader($file, $path, $size, null, $thumbSize);
                    $image                     = new AccountListingImage();
                    $image->account_listing_id = $listing->id;
                    $image->name               = $name;
                    $images[]                  = $image;
                } catch (\Exception $exp) {
                    return false;
                }
            }
            $listing->images()->saveMany($images);
        }
        return true;
    }

    protected function removeImages($request, $listing, $path)
    {
        $previousImages = $listing->images->pluck('id')->toArray();
        $imageToRemove  = array_values(array_diff($previousImages, $request->old ?? []));
        foreach ($imageToRemove as $item) {
            $listingImage   = AccountListingImage::find($item);
            fileManager()->removeFile($path . '/' . $listingImage->name);
            fileManager()->removeFile($path . '/thumb_' . $listingImage->name);
            $listingImage->delete();
        }
    }

    public function status($id)
    {
        $accountListing = AccountListing::where('user_id', auth()->user()->id)->findOrFail($id);

        if ($accountListing->status == Status::LISTING_SOLD || $accountListing->status == Status::LISTING_PENDING) {
            $notify[] = ['error', 'you can\'t change this status'];
            return back()->withNotify($notify);
        }

        $accountListing->status = $accountListing->status == Status::LISTING_ACTIVE ? Status::LISTING_INACTIVE : Status::LISTING_ACTIVE;
        $accountListing->save();

        $notify[] = ['success',  'Status change successfully'];
        return back()->withNotify($notify);
    }

    public function delete($id)
    {
        $accountListing = AccountListing::where('user_id', auth()->user()->id)->find($id);

        if (!$accountListing) {
            $notify[] = ['error', 'Account listing not found'];
            return back()->withNotify($notify);
        }

        if (!($accountListing->status == Status::LISTING_REJECTED)) {
            $notify[] = ['error', 'you can\'t delete this listing'];
            return back()->withNotify($notify);
        }

        $accountCredentials = AccountCredential::where('account_listing_id', $accountListing->id)->first();
        if ($accountCredentials) {
            $accountCredentials->delete();
        }

        $accountListingImages = AccountListingImage::where('account_listing_id', $accountListing->id)->get();

        if ($accountListingImages) {
            foreach ($accountListingImages as $accountListingImage) {
                fileManager()->removeFile(getFilePath('account_listing_images') . '/' . $accountListingImage->name);
                $accountListingImage->delete();
            }
        }

        fileManager()->removeFile(getFilePath('account_listing_thumb') . '/' . $accountListing->thumbnail_image);
        $accountListing->delete();

        $notify[] = ['success',  'Listing delete successfully'];
        return back()->withNotify($notify);
    }

    public function bid($id)
    {
        $pageTitle = 'Biddings Account';
        $accountListing = AccountListing::where('user_id', auth()->user()->id)->findOrFail($id);
        $biddingListings = BiddingListing::with('user')->where('account_listing_id', $accountListing->id)->paginate(getPaginate());
        return view('Template::user.account_listings.bidding', compact('pageTitle', 'biddingListings', 'accountListing'));
    }

    public function purchaseAccount()
    {
        $pageTitle = 'Purchases Account';
        $soldAccountListings = AccountListing::where('buyer_id', auth()->user()->id)->where('status', Status::LISTING_SOLD)->paginate(getPaginate());
        return view('Template::user.account_listings.sold_listing', compact('pageTitle', 'soldAccountListings'));
    }

    public function purchaseAccountDetails($id)
    {
        $pageTitle = 'Purchase Account Details';
        $soldAccountListing = AccountListing::where('buyer_id', auth()->user()->id)->where('status', Status::LISTING_SOLD)->findOrFail($id);
        return view('Template::user.account_listings.sold_listing_details', compact('pageTitle', 'soldAccountListing'));
    }

    public function myBid()
    {
        $pageTitle = 'My Bids';
        $biddings = BiddingListing::where('user_id', auth()->user()->id)->with('accountListing', function ($q) {
            $q->withMax('accountBidding', 'amount');
        })->whereHas('accountListing')->paginate(getPaginate());

        return view('Template::user.account_listings.my_bid', compact('pageTitle', 'biddings'));
    }

    public function cancelBid($id)
    {
        $user           = auth()->user();
        $bidding        = BiddingListing::where('user_id', $user->id)->findOrFail($id);
        $accountListing = AccountListing::where('status', Status::LISTING_ACTIVE)->findOrFail($bidding->account_listing_id);

        if ($accountListing) {
            $user->balance += $bidding->amount;
            $user->save();

            $transaction               = new Transaction();
            $transaction->user_id      = $user->id;
            $transaction->amount       = $bidding->amount;
            $transaction->post_balance = $user->balance;
            $transaction->charge       = 0;
            $transaction->trx_type     = '+';
            $transaction->details      = gs('cur_sym') . showAmount($bidding->amount) . ' Refunded for Canceled Bids';
            $transaction->trx          = getTrx();
            $transaction->remark       = 'bid_cancel';
            $transaction->save();

            if (userNotifyPermission($user, 'cancel_bid')) {
                notify($user, 'BID_CANCEL', [
                    'title'         => $accountListing->title,
                    'refund_amount' => showAmount($bidding->amount,currencyFormat:false),
                    'pricing_model' => $accountListing->pricing_model == Status::AUCTION ? 'Auction' : 'Fixed',
                ]);
            }

            $bidding->delete();
            $notify[] = ['success',  'Bid cancel successfully'];
            return back()->withNotify($notify);
        }
    }
}
