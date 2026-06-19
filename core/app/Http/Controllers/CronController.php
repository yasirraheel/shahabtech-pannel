<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\CronJob;
use App\Lib\CurlRequest;
use App\Constants\Status;
use App\Models\CronJobLog;
use App\Models\Transaction;
use App\Models\AccountListing;
use App\Models\BiddingListing;

class CronController extends Controller
{
    public function cron()
    {
        $general            = gs();
        $general->last_cron = now();
        $general->save();

        $crons = CronJob::with('schedule');

        if (request()->alias) {
            $crons->where('alias', request()->alias);
        } else {
            $crons->where('next_run', '<', now())->where('is_running', Status::YES);
        }
        $crons = $crons->get();
        foreach ($crons as $cron) {
            $cronLog              = new CronJobLog();
            $cronLog->cron_job_id = $cron->id;
            $cronLog->start_at    = now();
            if ($cron->is_default) {
                $controller = new $cron->action[0];
                try {
                    $method = $cron->action[1];
                    $controller->$method();
                } catch (\Exception $e) {
                    $cronLog->error = $e->getMessage();
                }
            } else {
                try {
                    CurlRequest::curlContent($cron->url);
                } catch (\Exception $e) {
                    $cronLog->error = $e->getMessage();
                }
            }
            $cron->last_run = now();
            $cron->next_run = now()->addSeconds($cron->schedule->interval);
            $cron->save();

            $cronLog->end_at = $cron->last_run;

            $startTime         = Carbon::parse($cronLog->start_at);
            $endTime           = Carbon::parse($cronLog->end_at);
            $diffInSeconds     = $startTime->diffInSeconds($endTime);
            $cronLog->duration = $diffInSeconds;
            $cronLog->save();
        }
        if (request()->target == 'all') {
            $notify[] = ['success', 'Cron executed successfully'];
            return back()->withNotify($notify);
        }
        if (request()->alias) {
            $notify[] = ['success', keyToTitle(request()->alias) . ' executed successfully'];
            return back()->withNotify($notify);
        }
    }


    public function auctionResult()
    {
        $accountListings = AccountListing::active()->pricingModelAuction()->where('auction_deadline', '<', today())->get();

        foreach ($accountListings as $accountListing) {

            $biddingWin    = BiddingListing::where('account_listing_id', $accountListing->id)->orderBy('amount', 'desc')->first();
            if (!$biddingWin) continue;
            $biddingLosses = BiddingListing::where('account_listing_id', $accountListing->id)->where('id', '!=', $biddingWin->id)->get();


            // For win
            if ($biddingWin) {
                $accountList            = AccountListing::find($accountListing->id);
                $accountList->buyer_id  = $biddingWin->user_id;
                $accountList->buy_price = $biddingWin->amount;
                $accountList->status    = Status::LISTING_SOLD;
                $accountList->save();

                if (userNotifyPermission($accountList->buyer, 'buy')) {
                    notify($biddingWin->user, 'ACCOUNT_BUYING', [
                        'title'         => $accountListing->title,
                        'buy_price'     => showAmount($biddingWin->amount,currencyFormat:false),
                        'pricing_model' => $accountListing->pricing_model == Status::AUCTION ? 'Auction' : 'Fixed',
                    ]);
                }

                $sellerUser           = User::find($biddingWin->accountListing->user_id);
                $sellerUser->balance += $biddingWin->amount;
                $sellerUser->save();

                $transaction               = new Transaction();
                $transaction->user_id      = $sellerUser->id;
                $transaction->amount       = $biddingWin->amount;
                $transaction->post_balance = $sellerUser->balance;
                $transaction->charge       = 0;
                $transaction->trx_type     = '+';
                $transaction->details      = 'Account Sale';
                $transaction->trx          = getTrx();
                $transaction->remark       = 'account_sell';
                $transaction->save();

                $totalCharge = gs('fixed_charge') + ((gs('percentage_charge') / 100) * $biddingWin->amount);

                $sellerUser->balance -= $totalCharge;
                $sellerUser->save();

                $transaction               = new Transaction();
                $transaction->user_id      = $sellerUser->id;
                $transaction->amount       = $totalCharge;
                $transaction->post_balance = $sellerUser->balance;
                $transaction->charge       = 0;
                $transaction->trx_type     = '-';
                $transaction->details      = 'Charge For Sale Account';
                $transaction->trx          = getTrx();
                $transaction->remark       = 'seller_fee';
                $transaction->save();

                if (userNotifyPermission($accountList->user, 'sell')) {
                    notify($sellerUser, 'ACCOUNT_SELLING', [
                        'title'         => $accountListing->title,
                        'sell_price'    => showAmount($accountListing->sell_price,currencyFormat:false),
                        'seller_fee'        => showAmount($totalCharge,currencyFormat:false),
                        'pricing_model' => $accountListing->pricing_model == Status::AUCTION ? 'Auction' : 'Fixed',
                    ]);
                }
            }

            // For Loss
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
                $transaction->details      = 'Refund for unsuccessful bids ' . gs('cur_sym') . showAmount($biddingLoss->amount);
                $transaction->trx          = getTrx();
                $transaction->remark       = 'bid_refund';
                $transaction->save();

                if (userNotifyPermission($lossUser, 'refund')) {
                    notify($biddingLoss->user, 'BID_REFUND', [
                        'title'         => $accountListing->title,
                        'refund_amount' => showAmount($biddingLoss->amount,currencyFormat:false),
                        'pricing_model' => $accountListing->pricing_model == Status::AUCTION ? 'Auction' : 'Fixed',
                    ]);
                }
            }
        }
    }
}
