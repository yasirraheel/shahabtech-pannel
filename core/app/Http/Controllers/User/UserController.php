<?php

namespace App\Http\Controllers\User;

use App\Models\Form;
use App\Models\User;
use App\Models\Deposit;
use App\Constants\Status;
use App\Lib\FormProcessor;
use App\Models\Withdrawal;
use App\Models\DeviceToken;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\ListingReport;
use App\Models\AccountListing;
use App\Models\BiddingListing;
use Illuminate\Validation\Rule;
use App\Lib\GoogleAuthenticator;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function home()
    {
        $pageTitle = 'Dashboard';
        $user      = auth()->user()->load('plan');

        // Platforms the user can access via their plan or specific account
        $platforms = [];
        if ($user->plan_id) {
            $platforms = \App\Models\SocialMedia::active()->whereHas('accountListing', function ($q) use ($user) {
                $q->where('plan_id', $user->plan_id)
                  ->where('status', \App\Constants\Status::LISTING_ACTIVE);
            })->get();
        } elseif (!empty($user->account_ids)) {
            $platforms = \App\Models\SocialMedia::active()->whereHas('accountListing', function ($q) use ($user) {
                $q->whereIn('id', $user->account_ids)
                  ->where('status', \App\Constants\Status::LISTING_ACTIVE);
            })->get();
        }

        $totalDeposit     = Deposit::where('user_id', $user->id)->where('status', Status::PAYMENT_SUCCESS)->sum('amount');
        $totalWithdrawals = Withdrawal::where('user_id', $user->id)->where('status', Status::PAYMENT_SUCCESS)->sum('amount');

        return view('Template::user.dashboard', compact('pageTitle', 'user', 'platforms', 'totalDeposit', 'totalWithdrawals'));
    }

    public function subscribePlan(Request $request, $id)
    {
        $plan = \App\Models\Plan::active()->findOrFail($id);
        $user = auth()->user();

        if ($user->plan_id == $plan->id) {
            $notify[] = ['error', 'You are already subscribed to this plan.'];
            return back()->withNotify($notify);
        }

        if ($user->balance < $plan->price) {
            $notify[] = ['error', 'Insufficient balance. Please deposit funds first.'];
            return redirect()->route('user.deposit.index')->withNotify($notify);
        }

        // Deduct balance
        $user->balance -= $plan->price;
        $user->plan_id = $plan->id;
        $user->save();

        if ($plan->price > 0) {
            $transaction = new Transaction();
            $transaction->user_id = $user->id;
            $transaction->amount = $plan->price;
            $transaction->post_balance = $user->balance;
            $transaction->charge = 0;
            $transaction->trx_type = '-';
            $transaction->details = 'Purchased Subscription Plan: ' . $plan->name;
            $transaction->trx = getTrx();
            $transaction->remark = 'plan_subscribe';
            $transaction->save();
        }

        $notify[] = ['success', 'Successfully subscribed to ' . $plan->name];
        return redirect()->route('user.home')->withNotify($notify);
    }

    public function depositHistory(Request $request)
    {
        $pageTitle = 'Deposit History';
        $deposits = auth()->user()->deposits()->searchable(['trx'])->with(['gateway'])->orderBy('id','desc')->paginate(getPaginate());
        return view('Template::user.deposit_history', compact('pageTitle', 'deposits'));
    }

    public function show2faForm()
    {
        $ga = new GoogleAuthenticator();
        $user = auth()->user();
        $secret = $ga->createSecret();
        $qrCodeUrl = $ga->getQRCodeGoogleUrl($user->username . '@' . gs('site_name'), $secret);
        $pageTitle = '2FA Security';
        return view('Template::user.twofactor', compact('pageTitle', 'secret', 'qrCodeUrl'));
    }

    public function create2fa(Request $request)
    {
        $user = auth()->user();
        $request->validate([
            'key' => 'required',
            'code' => 'required',
        ]);
        $response = verifyG2fa($user,$request->code,$request->key);
        if ($response) {
            $user->tsc = $request->key;
            $user->ts = Status::ENABLE;
            $user->save();
            $notify[] = ['success', 'Two factor authenticator activated successfully'];
            return back()->withNotify($notify);
        } else {
            $notify[] = ['error', 'Wrong verification code'];
            return back()->withNotify($notify);
        }
    }

    public function disable2fa(Request $request)
    {
        $request->validate([
            'code' => 'required',
        ]);

        $user = auth()->user();
        $response = verifyG2fa($user,$request->code);
        if ($response) {
            $user->tsc = null;
            $user->ts = Status::DISABLE;
            $user->save();
            $notify[] = ['success', 'Two factor authenticator deactivated successfully'];
        } else {
            $notify[] = ['error', 'Wrong verification code'];
        }
        return back()->withNotify($notify);
    }

    public function transactions()
    {
        $pageTitle = 'Transactions';
        $remarks = Transaction::distinct('remark')->orderBy('remark')->get('remark');

        $transactions = Transaction::where('user_id',auth()->id())->searchable(['trx'])->filter(['trx_type','remark'])->orderBy('id','desc')->paginate(getPaginate());

        return view('Template::user.transactions', compact('pageTitle','transactions','remarks'));
    }

    public function kycForm()
    {
        if (auth()->user()->kv == Status::KYC_PENDING) {
            $notify[] = ['error','Your KYC is under review'];
            return to_route('user.home')->withNotify($notify);
        }
        if (auth()->user()->kv == Status::KYC_VERIFIED) {
            $notify[] = ['error','You are already KYC verified'];
            return to_route('user.home')->withNotify($notify);
        }
        $pageTitle = 'KYC Form';
        $form = Form::where('act','kyc')->first();
        return view('Template::user.kyc.form', compact('pageTitle','form'));
    }

    public function kycData()
    {
        $user = auth()->user();
        $pageTitle = 'KYC Data';
        return view('Template::user.kyc.info', compact('pageTitle','user'));
    }

    public function kycSubmit(Request $request)
    {
        $form = Form::where('act','kyc')->firstOrFail();
        $formData = $form->form_data;
        $formProcessor = new FormProcessor();
        $validationRule = $formProcessor->valueValidation($formData);
        $request->validate($validationRule);
        $user = auth()->user();
        foreach (@$user->kyc_data ?? [] as $kycData) {
            if ($kycData->type == 'file') {
                fileManager()->removeFile(getFilePath('verify').'/'.$kycData->value);
            }
        }
        $userData = $formProcessor->processFormData($request, $formData);
        $user->kyc_data = $userData;
        $user->kyc_rejection_reason = null;
        $user->kv = Status::KYC_PENDING;
        $user->save();

        $notify[] = ['success','KYC data submitted successfully'];
        return to_route('user.home')->withNotify($notify);

    }

    public function userData()
    {
        $user = auth()->user();

        if ($user->profile_complete == Status::YES) {
            return to_route('user.home');
        }

        $pageTitle  = 'User Data';
        $info       = json_decode(json_encode(getIpInfo()), true);
        $mobileCode = @implode(',', $info['code']);
        $countries  = json_decode(file_get_contents(resource_path('views/partials/country.json')));

        return view('Template::user.user_data', compact('pageTitle', 'user', 'countries', 'mobileCode'));
    }

    public function userDataSubmit(Request $request)
    {

        $user = auth()->user();

        if ($user->profile_complete == Status::YES) {
            return to_route('user.home');
        }

        $countryData  = (array)json_decode(file_get_contents(resource_path('views/partials/country.json')));
        $countryCodes = implode(',', array_keys($countryData));
        $mobileCodes  = implode(',', array_column($countryData, 'dial_code'));
        $countries    = implode(',', array_column($countryData, 'country'));

        $request->validate([
            'country_code' => 'required|in:' . $countryCodes,
            'country'      => 'required|in:' . $countries,
            'mobile_code'  => 'required|in:' . $mobileCodes,
            'username'     => 'required|unique:users|min:6',
            'mobile'       => ['required','regex:/^([0-9]*)$/',Rule::unique('users')->where('dial_code',$request->mobile_code)],
        ]);


        if (preg_match("/[^a-z0-9_]/", trim($request->username))) {
            $notify[] = ['info', 'Username can contain only small letters, numbers and underscore.'];
            $notify[] = ['error', 'No special character, space or capital letters in username.'];
            return back()->withNotify($notify)->withInput($request->all());
        }

        $user->country_code = $request->country_code;
        $user->mobile       = $request->mobile;
        $user->username     = $request->username;


        $user->address = $request->address;
        $user->city = $request->city;
        $user->state = $request->state;
        $user->zip = $request->zip;
        $user->country_name = @$request->country;
        $user->dial_code = $request->mobile_code;

        $user->profile_complete = Status::YES;
        $user->save();

        return to_route('user.home');
    }


    public function addDeviceToken(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'token' => 'required',
        ]);

        if ($validator->fails()) {
            return ['success' => false, 'errors' => $validator->errors()->all()];
        }

        $deviceToken = DeviceToken::where('token', $request->token)->first();

        if ($deviceToken) {
            return ['success' => true, 'message' => 'Already exists'];
        }

        $deviceToken          = new DeviceToken();
        $deviceToken->user_id = auth()->user()->id;
        $deviceToken->token   = $request->token;
        $deviceToken->is_app  = Status::NO;
        $deviceToken->save();

        return ['success' => true, 'message' => 'Token saved successfully'];
    }

    public function downloadAttachment($fileHash)
    {
        $filePath = decrypt($fileHash);
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        $title = slug(gs('site_name')).'- attachments.'.$extension;
        try {
            $mimetype = mime_content_type($filePath);
        } catch (\Exception $e) {
            $notify[] = ['error','File does not exists'];
            return back()->withNotify($notify);
        }
        header('Content-Disposition: attachment; filename="' . $title);
        header("Content-Type: " . $mimetype);
        return readfile($filePath);
    }

    public function directPayment(Request $request)
    {
        $request->validate([
            'amount'             => $request->pricing_model == Status::AUCTION ? 'required|numeric|gt:0' : 'nullable',
            'account_listing_id' => 'required|exists:account_listings,id',
            'payment_type'       => 'required|string|in:balance,deposit',
            'submit_type'        => 'required|string|in:buy,bid',
        ]);

        $accountListing = AccountListing::where('status', Status::LISTING_ACTIVE)->findOrFail($request->account_listing_id);
        $myBid          = BiddingListing::where('user_id', auth()->id())->where('account_listing_id', $request->account_listing_id)->first();

        if ($myBid) {
            $requestAmount = $myBid->amount + $request->amount;
            $oldBid        = $myBid->amount;
        } else {
            $requestAmount = $request->amount;
            $oldBid        = 0;
        }

        $user        = auth()->user();
        $requestType = $request->submit_type;
        $trx         = getTrx();


        if ($request->payment_type == 'balance' && $requestType == 'bid') {

            $returnData = $this->auctionValidation($accountListing, $requestAmount, $user);

            if (!$returnData['success']) {
                $notify[] = ['error', $returnData['message']];
                return back()->withNotify($notify);
            }

            $biddingListing = BiddingListing::where('user_id', $user->id)->where('account_listing_id', $accountListing->id)->first();

            if ($biddingListing) {
                if ($requestAmount <= $biddingListing->amount) {
                    $notify[] = ['error', 'Please provide a greater amount than the previous ' . showAmount($biddingListing->amount) . ' amount'];
                    return back()->withNotify($notify);
                }

                $newAmount = $requestAmount - $biddingListing->amount;

                if ($user->balance < $newAmount) {
                    $notify[] = ['error', 'You don\'t have sufficient balance'];
                    return back()->withNotify($notify)->withInput();
                }

                $notifyMessage = 'Bid update has been successful.';

                $user->balance -=  $newAmount;
                $user->save();

                $transaction               = new Transaction();
                $transaction->user_id      = $user->id;
                $transaction->amount       = $newAmount;
                $transaction->post_balance = $user->balance;
                $transaction->charge       = 0;
                $transaction->trx_type     = '-';
                $transaction->details      = "Update Bid For Account Buy";
                $transaction->trx          = $trx;
                $transaction->remark       = 'bid_update';
                $transaction->save();
            } else {

                if ($user->balance < $requestAmount) {
                    $notify[] = ['error', 'You don\'t have sufficient balance'];
                    return back()->withNotify($notify)->withInput();
                }

                $biddingListing                     = new BiddingListing();
                $biddingListing->user_id            = $user->id;
                $biddingListing->account_listing_id = $accountListing->id;

                $notifyMessage = 'The bid has been placed successfully.';

                $user->balance -=  $requestAmount;
                $user->save();

                $transaction               = new Transaction();
                $transaction->user_id      = $user->id;
                $transaction->amount       = $requestAmount;
                $transaction->post_balance = $user->balance;
                $transaction->charge       = 0;
                $transaction->trx_type     = '-';
                $transaction->details      = "Bid For Account Buy";
                $transaction->trx          = $trx;
                $transaction->remark       = 'bid';
                $transaction->save();
            }

            $biddingListing->amount = $requestAmount;
            $biddingListing->save();


            if (userNotifyPermission($user, 'bid')) {
                notify($user, 'BID_PLACE', [
                    'title'          => $accountListing->title,
                    'bidding_amount' => showAmount($requestAmount,currencyFormat:false),
                    'pricing_model'  => $accountListing->pricing_model == Status::AUCTION ? 'Auction' : 'Fixed',
                ]);
            }

            $notify[] = ['success', $notifyMessage];
            return back()->withNotify($notify)->withInput();
        }

        //for balance & buy
        if ($request->payment_type == 'balance' && $requestType == 'buy') {

            if ($user->balance < $accountListing->sell_price) {
                $notify[] = ['error', 'You don\'t have sufficient balance'];
                return back()->withNotify($notify)->withInput();
            }

            $user->balance -= $accountListing->sell_price;
            $user->save();

            $accountListing->buyer_id  = $user->id;
            $accountListing->buy_price = $accountListing->sell_price;
            $accountListing->status    = Status::LISTING_SOLD;
            $accountListing->save();

            $transaction               = new Transaction();
            $transaction->user_id      = $user->id;
            $transaction->amount       = $accountListing->sell_price;
            $transaction->post_balance = $user->balance;
            $transaction->charge       = 0;
            $transaction->trx_type     = '-';
            $transaction->details      = 'Account Buy';
            $transaction->trx          = $trx;
            $transaction->remark       = 'account_buy';
            $transaction->save();

            if (userNotifyPermission($user, 'buy')) {
                notify($user, 'ACCOUNT_BUYING', [
                    'title'         => $accountListing->title,
                    'buy_price'     => showAmount($accountListing->sell_price,currencyFormat:false),
                    'pricing_model' => $accountListing->pricing_model == Status::AUCTION ? 'Auction' : 'Fixed',
                ]);
            }

            $sellerUser           = User::find($accountListing->user_id);
            $sellerUser->balance += $accountListing->sell_price;
            $sellerUser->save();

            $transaction               = new Transaction();
            $transaction->user_id      = $sellerUser->id;
            $transaction->amount       = $accountListing->sell_price;
            $transaction->post_balance = $sellerUser->balance;
            $transaction->charge       = 0;
            $transaction->trx_type     = '+';
            $transaction->details      = 'Account Sale';
            $transaction->trx          = $trx;
            $transaction->remark       = 'account_sell';
            $transaction->save();


            $totalCharge          = gs('fixed_charge') + ((gs('percentage_charge') / 100) * $accountListing->sell_price);
            $sellerUser->balance -= $totalCharge;
            $sellerUser->save();

            $transaction               = new Transaction();
            $transaction->user_id      = $sellerUser->id;
            $transaction->amount       = $totalCharge;
            $transaction->post_balance = $sellerUser->balance;
            $transaction->charge       = 0;
            $transaction->trx_type     = '-';
            $transaction->details      = 'Charge For Account Sell';
            $transaction->trx          = $trx;
            $transaction->remark       = 'seller_fee';
            $transaction->save();

            if (userNotifyPermission($sellerUser, 'sell')) {
                notify($sellerUser, 'ACCOUNT_SELLING', [
                    'title'         => $accountListing->title,
                    'sell_price'    => showAmount($accountListing->sell_price,currencyFormat:false),
                    'seller_fee'    => showAmount($totalCharge,currencyFormat:false),
                    'pricing_model' => $accountListing->pricing_model == Status::AUCTION ? 'Auction' : 'Fixed',
                ]);
            }

            $biddingLosses = BiddingListing::where('account_listing_id', $accountListing->id)->get();

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
                $transaction->details      = 'Refund For Unsuccessful Bids';
                $transaction->trx          = $trx;
                $transaction->remark       = 'bid_refund';
                $transaction->save();

                if (userNotifyPermission($biddingLoss->user, 'refund')) {
                    notify($biddingLoss->user, 'BID_REFUND', [
                        'title'         => $accountListing->title,
                        'refund_amount' => showAmount($biddingLoss->amount,currencyFormat:false),
                        'pricing_model' => $accountListing->pricing_model == Status::AUCTION ? 'Auction' : 'Fixed',
                    ]);
                }
            }

            $notify[] = ['success', 'Purchase this account has been successfully'];
            return back()->withNotify($notify)->withInput();
        }


        if ($accountListing->pricing_model == Status::AUCTION) {

            if ($requestType == 'bid') {

                $returnData = $this->auctionValidation($accountListing, $requestAmount, $user);

                if (!$returnData['success']) {
                    $notify[] = ['error', $returnData['message']];
                    return back()->withNotify($notify);
                }

                $biddingListing = BiddingListing::where('user_id', $user->id)->where('account_listing_id', $accountListing->id)->first();

                if ($biddingListing) {
                    if ($requestAmount <= $biddingListing->amount) {
                        $notify[] = ['error', 'Please provide a greater amount than the previous ' . showAmount($biddingListing->amount) . ' amount'];
                        return back()->withNotify($notify);
                    }
                }
            } elseif ($requestType == 'buy') {
                $requestAmount = getAmount($accountListing->sell_price);
            }
        }

        session()->put(['accountListing' => $accountListing->id, 'requestAmount' => ($requestAmount - $oldBid), 'requestType' => $requestType]);

        return to_route('user.deposit.index', ['paymentDeposit' => true]);
    }

    protected function auctionValidation($accountListing, $requestAmount, $user)
    {

        if ($accountListing->sell_price < $requestAmount) {
            return [
                'success' => false,
                'message' => 'Maximum bidding amount is ' . showAmount($accountListing->sell_price) . ' for this listing'
            ];
        }

        if ($accountListing->min_price > $requestAmount) {
            return [
                'success' => false,
                'message' =>  'Minimum bidding amount is ' . showAmount($accountListing->min_price) . ' for this listing'
            ];
        }

        if ($accountListing->user_id == $user->id) {
            return [
                'success' => false,
                'message' => 'This listing belongs to your account'
            ];
        }

        $maxBid = BiddingListing::where('account_listing_id', $accountListing->id)->max('amount');
        if ($requestAmount <= $maxBid) {
            return [
                'success' => false,
                'message' => 'Current bid amount is ' . showAmount($maxBid)
            ];
        }

        $biddingSellPrice = BiddingListing::where('account_listing_id', $accountListing->id)->where('amount', $accountListing->sell_price)->first();

        if ($biddingSellPrice) {
            return [
                'success' => false,
                'message' =>  'Unfortunately, you won\'t be able to bid on this listing. There is already a bidding maximum price'
            ];
        }

        return [
            'success' => true,
        ];
    }


    public function report(Request $request)
    {
        $request->validate([
            'report' => 'required',
            'listing_id' => 'required|exists:account_listings,id'
        ]);

        if (!auth()->check()) {
            $notify[] = ['error', 'please log in to your account'];
            return back()->withNotify($notify);
        }

        $report = new ListingReport();
        $report->user_id = auth()->user()->id;
        $report->account_listing_id = $request->listing_id;
        $report->report = $request->report;
        $report->save();
        $notify[] = ['success', 'Your report has been successfully submitted'];
        return back()->withNotify($notify);
    }

}
