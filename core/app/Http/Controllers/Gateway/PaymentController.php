<?php

namespace App\Http\Controllers\Gateway;

use App\Models\User;
use App\Models\Deposit;
use App\Constants\Status;
use App\Lib\FormProcessor;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\AccountListing;
use App\Models\BiddingListing;
use App\Models\GatewayCurrency;
use App\Models\AdminNotification;
use App\Http\Controllers\Controller;

class PaymentController extends Controller
{
    public function deposit()
    {
        $gatewayCurrency = GatewayCurrency::whereHas('method', function ($gate) {
            $gate->where('status', Status::ENABLE);
        })->with('method')->orderby('name')->get();
        $pageTitle = 'Deposit Methods';
        return view('Template::user.payment.deposit', compact('gatewayCurrency', 'pageTitle'));
    }

    public function depositInsert(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|gt:0',
            'gateway' => 'required',
            'currency' => 'required',
        ]);

        if (session()->get('accountListing') && session()->get('requestAmount') && session()->get('requestType')) {
            if ($request->amount != session()->get('requestAmount')) {
                $notify[] = ['error', 'Invalid amount'];
                return back()->withNotify($notify);
            };

            $accountListingId = session()->get('accountListing');
            $requestType = session()->get('requestType');
        }

        $user = auth()->user();

        $gate = GatewayCurrency::whereHas('method', function ($gate) {
            $gate->where('status', Status::ENABLE);
        })->where('method_code', $request->gateway)->where('currency', $request->currency)->first();
        if (!$gate) {
            $notify[] = ['error', 'Invalid gateway'];
            return back()->withNotify($notify);
        }

        if ($gate->min_amount > $request->amount || $gate->max_amount < $request->amount) {
            $notify[] = ['error', 'Please follow deposit limit'];
            return back()->withNotify($notify);
        }

        $charge = $gate->fixed_charge + ($request->amount * $gate->percent_charge / 100);
        $payable = $request->amount + $charge;
        $finalAmount = $payable * $gate->rate;

        $data = new Deposit();
        $data->user_id = $user->id;
        $data->account_listing_id = $accountListingId ?? 0;
        $data->request_type = $requestType ?? null;
        $data->method_code = $gate->method_code;
        $data->method_currency = strtoupper($gate->currency);
        $data->amount = $request->amount;
        $data->charge = $charge;
        $data->rate = $gate->rate;
        $data->final_amount = $finalAmount;
        $data->btc_amount = 0;
        $data->btc_wallet = "";
        $data->trx = getTrx();
        $data->success_url = urlPath('user.deposit.history');
        $data->failed_url = urlPath('user.deposit.history');
        $data->save();
        session()->put('Track', $data->trx);
        return to_route('user.deposit.confirm');
    }


    public function depositConfirm()
    {
        $track = session()->get('Track');
        $deposit = Deposit::where('trx', $track)->where('status',Status::PAYMENT_INITIATE)->orderBy('id', 'DESC')->with('gateway')->firstOrFail();

        if ($deposit->method_code >= 1000) {
            return to_route('user.deposit.manual.confirm');
        }


        $dirName = $deposit->gateway->alias;
        $new = __NAMESPACE__ . '\\' . $dirName . '\\ProcessController';

        $data = $new::process($deposit);
        $data = json_decode($data);


        if (isset($data->error)) {
            $notify[] = ['error', $data->message];
            return back()->withNotify($notify);
        }
        if (isset($data->redirect)) {
            return redirect($data->redirect_url);
        }

        // for Stripe V3
        if(@$data->session){
            $deposit->btc_wallet = $data->session->id;
            $deposit->save();
        }

        $pageTitle = 'Payment Confirm';
        return view("Template::$data->view", compact('data', 'pageTitle', 'deposit'));
    }


    public static function userDataUpdate($deposit,$isManual = null)
    {
        if ($deposit->status == Status::PAYMENT_INITIATE || $deposit->status == Status::PAYMENT_PENDING) {
            $deposit->status = Status::PAYMENT_SUCCESS;
            $deposit->save();

            $user = User::find($deposit->user_id);
            $user->balance += $deposit->amount;
            $user->save();

            $methodName = $deposit->methodName();

            $transaction = new Transaction();
            $transaction->user_id = $deposit->user_id;
            $transaction->amount = $deposit->amount;
            $transaction->post_balance = $user->balance;
            $transaction->charge = $deposit->charge;
            $transaction->trx_type = '+';
            $transaction->details = 'Deposit Via ' . $methodName;
            $transaction->trx = $deposit->trx;
            $transaction->remark = 'deposit';
            $transaction->save();

            if (!$isManual) {
                $adminNotification = new AdminNotification();
                $adminNotification->user_id = $user->id;
                $adminNotification->title = 'Deposit successful via '.$methodName;
                $adminNotification->click_url = urlPath('admin.deposit.successful');
                $adminNotification->save();
            }

            notify($user, $isManual ? 'DEPOSIT_APPROVE' : 'DEPOSIT_COMPLETE', [
                'method_name' => $methodName,
                'method_currency' => $deposit->method_currency,
                'method_amount' => showAmount($deposit->final_amount,currencyFormat:false),
                'amount' => showAmount($deposit->amount,currencyFormat:false),
                'charge' => showAmount($deposit->charge,currencyFormat:false),
                'rate' => showAmount($deposit->rate,currencyFormat:false),
                'trx' => $deposit->trx,
                'post_balance' => showAmount($user->balance)
            ]);

             //for account listing data
             if ($deposit->account_listing_id) {

                $accountListing = AccountListing::find($deposit->account_listing_id);

                if ($accountListing->pricing_model == Status::AUCTION && $accountListing->status == Status::LISTING_ACTIVE) {

                    if ($deposit->request_type == 'bid') {
                        $biddingListing = BiddingListing::where('user_id', $user->id)->where('account_listing_id', $accountListing->id)->first();

                        if ($biddingListing) {

                            $biddingListing->amount += $deposit->amount;
                            $biddingListing->save();

                            $newAmount      = $deposit->amount;
                            $user->balance -= $newAmount;
                            $user->save();

                            $transaction               = new Transaction();
                            $transaction->user_id      = $user->id;
                            $transaction->amount       = $newAmount;
                            $transaction->post_balance = $user->balance;
                            $transaction->charge       = 0;
                            $transaction->trx_type     = '-';
                            $transaction->details      = 'Update Bid For Account Buy';
                            $transaction->trx          = $deposit->trx;
                            $transaction->remark       = 'bid';
                            $transaction->save();
                        } else {

                            $biddingListing                     = new BiddingListing();
                            $biddingListing->user_id            = $user->id;
                            $biddingListing->account_listing_id = $accountListing->id;
                            $biddingListing->amount             = $deposit->amount;
                            $biddingListing->save();

                            $user->balance -= $accountListing->sell_price;
                            $user->save();

                            $transaction               = new Transaction();
                            $transaction->user_id      = $deposit->user_id;
                            $transaction->amount       = $accountListing->sell_price;
                            $transaction->post_balance = $user->balance;
                            $transaction->charge       = 0;
                            $transaction->trx_type     = '-';
                            $transaction->details      = 'Bid For Account Buy';
                            $transaction->trx          = $deposit->trx;
                            $transaction->remark       = 'bid';
                            $transaction->save();
                        }

                        if (userNotifyPermission($user, 'bid')) {
                            notify($user, 'BID_PLACE', [
                                'title'          => $accountListing->title,
                                'bidding_amount' => showAmount($deposit->amount,currencyFormat:false),
                                'pricing_model'  => $accountListing->pricing_model == Status::AUCTION ? 'Auction' : 'Fixed',
                            ]);
                        }
                    } elseif ($deposit->request_type == 'buy') {

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
                        $transaction->trx          = $deposit->trx;
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
                        $transaction->trx          = $deposit->trx;
                        $transaction->remark       = 'account_sell';
                        $transaction->save();


                        $totalCharge = gs('fixed_charge') + ((gs('percentage_charge') / 100) * $accountListing->sell_price);
                        $sellerUser->balance -= $totalCharge;
                        $sellerUser->save();

                        $transaction               = new Transaction();
                        $transaction->user_id      = $sellerUser->id;
                        $transaction->amount       = $totalCharge;
                        $transaction->post_balance = $sellerUser->balance;
                        $transaction->charge       = 0;
                        $transaction->trx_type     = '-';
                        $transaction->details      = 'Charge For Account Sale';
                        $transaction->trx          = getTrx();
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
                            $transaction->details      = 'Refund For Unsuccessful Bids ' . gs('cur_sym') . showAmount($biddingLoss->amount);
                            $transaction->trx          = getTrx();
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
                    }
                }

                if ($accountListing->pricing_model == Status::FIXED) {
                    if ($accountListing->status == Status::LISTING_ACTIVE) {
                        self::fixedUserData($accountListing, $user, $deposit->trx);
                    }
                }
            }
        }
    }

    public function manualDepositConfirm()
    {
        $track = session()->get('Track');
        $data = Deposit::with('gateway')->where('status', Status::PAYMENT_INITIATE)->where('trx', $track)->first();
        abort_if(!$data, 404);
        if ($data->method_code > 999) {
            $pageTitle = 'Confirm Deposit';
            $method = $data->gatewayCurrency();
            $gateway = $method->method;
            return view('Template::user.payment.manual', compact('data', 'pageTitle', 'method','gateway'));
        }
        abort(404);
    }

    public function manualDepositUpdate(Request $request)
    {
        $track = session()->get('Track');
        $data = Deposit::with('gateway')->where('status', Status::PAYMENT_INITIATE)->where('trx', $track)->first();
        abort_if(!$data, 404);
        $gatewayCurrency = $data->gatewayCurrency();
        $gateway = $gatewayCurrency->method;
        $formData = $gateway->form->form_data;

        $formProcessor = new FormProcessor();
        $validationRule = $formProcessor->valueValidation($formData);
        $request->validate($validationRule);
        $userData = $formProcessor->processFormData($request, $formData);


        $data->detail = $userData;
        $data->status = Status::PAYMENT_PENDING;
        $data->save();


        $adminNotification = new AdminNotification();
        $adminNotification->user_id = $data->user->id;
        $adminNotification->title = 'Deposit request from '.$data->user->username;
        $adminNotification->click_url = urlPath('admin.deposit.details',$data->id);
        $adminNotification->save();

        notify($data->user, 'DEPOSIT_REQUEST', [
            'method_name' => $data->gatewayCurrency()->name,
            'method_currency' => $data->method_currency,
            'method_amount' => showAmount($data->final_amount,currencyFormat:false),
            'amount' => showAmount($data->amount,currencyFormat:false),
            'charge' => showAmount($data->charge,currencyFormat:false),
            'rate' => showAmount($data->rate,currencyFormat:false),
            'trx' => $data->trx
        ]);

        $notify[] = ['success', 'You have deposit request has been taken'];
        return to_route('user.deposit.history')->withNotify($notify);
    }
    
    static function fixedUserData($accountListing, $user, $trx)
    {

        $user->balance -= $accountListing->sell_price;
        $user->save();

        $accountListing->buyer_id  = $user->id;
        $accountListing->buy_price = $accountListing->sell_price;
        $accountListing->status    = Status::LISTING_SOLD;
        $accountListing->save();


        if (userNotifyPermission($user, 'buy')) {
            notify($user, 'ACCOUNT_BUYING', [
                'title'         => $accountListing->title,
                'buy_price'    => showAmount($accountListing->sell_price,currencyFormat:false),
                'pricing_model' => $accountListing->pricing_model == Status::AUCTION ? 'Auction' : 'Fixed',
            ]);
        }

        $transaction               = new Transaction();
        $transaction->user_id      = $user->id;
        $transaction->amount       = $accountListing->sell_price;
        $transaction->post_balance = $user->balance;
        $transaction->charge       = 0;
        $transaction->trx_type     = '-';
        $transaction->details      = 'Account Buy';
        $transaction->trx          = $trx;
        $transaction->remark       = 'acoount_buy';
        $transaction->save();

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
        $transaction->details      = "Charge For Account Sale";
        $transaction->trx          = getTrx();
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
    }

}
