<?php

/**
 * Created by Studio.
 * User: mosesgathecha
 * Date: 13/05/2018
 * Time: 22:00
 */

namespace App\Helpers;

use App\WalletDebit;
use App\WalletCredit;
use App\WalletRefund;
use App\WalletAccount;
use App\WalletBalance;
use App\Events\WalletEvent;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WalletHelper
{
    public static function openAccount($userId, $accountNumber, $wallet_id)
    {
        $accountBalance = new WalletBalance();
        $accountBalance->user_id = $userId;
        $accountBalance->wallet_id = $wallet_id;
        $accountBalance->total_credit = 0;
        $accountBalance->total_debit = 0;
        $accountBalance->account_number = $accountNumber;
        $accountBalance->save();
        return $accountBalance;
    }

    //IP System

    public static function walletBalance($userId)
    {
        self::getAccountByUserId($userId);
        $accountBalance = WalletBalance::where('user_id', $userId)->first();
        return ($accountBalance->total_credit - $accountBalance->total_debit);
    }

    public static function walletBalanceData($userId)
    {
        $accountBalanceHistory = WalletBalance::where('user_id', $userId)->first();
        return $accountBalanceHistory;
    }

    public static function creditWallet($userId, $moneyInId, $amountCredited, $amountSource, $attachedBooking = '')
    {
        $walletAccount = self::getAccountByUserId($userId);
        $creditAccount = new WalletCredit();
        $creditAccount->user_id = $userId;
        $creditAccount->wallet_id = $walletAccount->id;
        $creditAccount->money_in_id = $moneyInId;
        $creditAccount->account_number = $walletAccount->account_number;
        $creditAccount->amount = $amountCredited;
        $creditAccount->source = $amountSource;
        $creditAccount->save();

        self::updateWallet($userId);
        event(new WalletEvent($creditAccount, "Credit Account", $attachedBooking));
        return $creditAccount;
    }

    public static function debitWallet($userId, $moneyOutId, $debitAmount, $debitDestination, $bookingReference)
    {
        $booking = self::getBooking($bookingReference);
        if (!$booking) {
            return;
        }


        $bookingBalance = self::bookingBalance($booking->id);
        $walletAccount = self::getAccountByUserId($userId);
        $walletDebit = new WalletDebit();
        $walletDebit->user_id = $userId;
        $walletDebit->wallet_id = $walletAccount->id;
        $walletDebit->account_number = $walletAccount->account_number;
        $walletDebit->money_out_id = $moneyOutId;
        $walletDebit->amount = ($bookingBalance < $debitAmount) ? $bookingBalance : $debitAmount;
        $walletDebit->destination = $debitDestination;
        $walletDebit->destination_id = $booking->id;
        $walletDebit->destination_reference = $booking->booking_reference;
        $walletDebit->debit_status = 'complete';
        $walletDebit->debit_required_approval = 0;
        $walletDebit->save();
        self::updateWallet($userId);
        return $walletDebit;
    }

    /**
     * @param $userId
     * @return mixed
     */
    public static function getAccountByUserId($userId)
    {
        $wallet = WalletAccount::where('user_id', $userId)->first();
        self::updateWallet($userId);
        return $wallet;
    }

    public static function updateWallet($userId)
    {
        $walletBalanceData = self::walletBalanceData($userId);
        $total_credit = DB::table('lp_wallet_credit')->where('user_id', $userId)->where('account_type', 'booking')->sum('amount');
        $total_debit = DB::table('lp_wallet_debit')->where('user_id', $userId)->where('account_type', 'booking')->sum('amount');
        if (!is_null($walletBalanceData)) {
            $walletBalanceData->total_credit = is_null($total_credit) ? 0 : $total_credit;
            $walletBalanceData->total_debit = is_null($total_debit) ? 0 : $total_debit;
            $walletBalanceData->save();
        } else {
            $walletBalanceData = self::walletBalanceData($userId);
            $walletBalanceData->total_credit = is_null($total_credit) ? 0 : $total_credit;
            $walletBalanceData->total_debit = is_null($total_debit) ? 0 : $total_debit;
            $walletBalanceData->save();
        }
    }


    public static function getUser($userId)
    {
        $user = DB::table('lp_customers')->where('user_id', $userId)->first();
        return $user;
    }

    public static function getCustomer($phone)
    {
        $customer = DB::table('lp_customers')->where('phone_number_1', $phone)->first();
        return $customer;
    }

    public static function checkWalletExists($user_id)
    {
        $wallet = WalletAccount::where('user_id', $user_id)->first();
        return $wallet;
    }

    public static function bookingExist($bookingReference)
    {
        return DB::table('product_booking')->where('booking_reference', $bookingReference)->exists();
    }

    public static function getBooking($bookingReference)
    {
        return DB::table('product_booking')->where('booking_reference', $bookingReference)->first();
    }

    public static function hasOneBooking($userId)
    {
        return DB::table('product_booking')
            ->where('booking_status', 'open')
            ->where('user_id', $userId)
            ->whereNull('deleted_at')
            ->get();
    }

    public static function moneyInExist($id)
    {
        return DB::table('lp_money_in')->where('id', $id)->exists();
    }

    public static function moneyInCheck($id)
    {
        Log::debug('checking for money exist id ' . $id);
        $result =  DB::table('lp_money_in')->where('id', $id)->first();
        return $result;
    }

    public static function bookingBalance($bookingID)
    {
        $amountPayable = DB::table('product_booking')->where('id', $bookingID)->value('booking_price');
        $amountPaid = DB::table('product_booking_payments')->where('booking_id', $bookingID)->sum('payment_amount');
        return (floatval($amountPayable) - floatval($amountPaid));
    }

    public function refundFromWallet(Request $request, $user_id, $refunder)
    {
        $wallet = WalletAccount::where('user_id', $user_id)->first();
        if (is_null($wallet)) {
            return json_response()->error('Wallet account not found')
                ->setStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $wallet_balance = (new self)->walletBalance($user_id);
        $refund_amount = $request->refund_amount;
        if ($refund_amount > $wallet_balance) {
            return json_response()->error('Refund amount has exceeded current balance')
                ->setStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->refundDebit($user_id, $refund_amount);
        WalletRefund::create([
            'wallet_user_id' => $user_id,
            'refund_amount' => $refund_amount,
            'refunded_by' => $refunder
        ]);
        return 'Refund was successful';
    }

    public function refundDebit($user_id, $amount)
    {
        $walletAccount = (new self)->getAccountByUserId($user_id);
        $walletDebit = new WalletDebit();
        $walletDebit->user_id = $user_id;
        $walletDebit->wallet_id = $walletAccount->id;
        $walletDebit->account_number = $walletAccount->account_number;
        $walletDebit->money_out_id = 0;
        $walletDebit->amount = $amount;
        $walletDebit->destination = 'refund';
        $walletDebit->destination_id = 0;
        $walletDebit->save();
        (new self)->updateWallet($user_id);
    }
}
