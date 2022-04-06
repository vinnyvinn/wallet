<?php

/**
 * Created by Studio.
 * User: mosesgathecha
 * Date: 13/05/2018
 * Time: 22:00
 */

namespace App\Helpers;


use App\CommissionUser;
use App\SuperWalletAccount;
use App\SuperWalletBalance;
use App\SuperWalletCredit;
use App\SuperWalletDebit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SuperWalletHelper
{

    public static function openAccount($userId, $accountNumber, $wallet_id)
    {
        $accountBalance = new SuperWalletBalance();
        $accountBalance->user_id = $userId;
        $accountBalance->super_wallet_id = $wallet_id;
        $accountBalance->account_number = $accountNumber;
        $accountBalance->total_credit = 0;
        $accountBalance->total_debit = 0;
        $accountBalance->save();
        self::createPromoterCommission($userId, 1);
        return $accountBalance;
    }

    //IP System

    public static function walletBalance($userId)
    {
        self::getAccountByUserId($userId);
        $accountBalance = SuperWalletBalance::where('user_id', $userId)->first();
        return floatval(($accountBalance->total_credit - $accountBalance->total_debit));
    }

    public static function walletBalanceData($userId)
    {
        $accountBalanceHistory = SuperWalletBalance::where('user_id', $userId)->first();
        return $accountBalanceHistory;
    }

    public static function creditWallet($userId, $productId, $bookingId, $bookingReference, $amount, $amountSource)
    {
        $superWalletAccount = self::getAccountByUserId($userId);
        $creditAccount = new SuperWalletCredit();
        $creditAccount->user_id = $userId;
        $creditAccount->super_wallet_id = $superWalletAccount->id;
        $creditAccount->account_number = $superWalletAccount->account_number;
        $creditAccount->product_id = $productId;
        $creditAccount->booking_id = $bookingId;
        $creditAccount->booking_reference = $bookingReference;
        $creditAccount->amount = $amount;
        $creditAccount->source = $amountSource;
        $creditAccount->save();
        self::updateWallet($userId);
        return $creditAccount;
    }

    public static function debitWallet($userId, $debitAmount, $debitDestination, $destinationId)
    {
        $superWalletAccount = self::getAccountByUserId($userId);
        $superWalletDebit = new SuperWalletDebit();
        $superWalletDebit->user_id = $userId;
        $superWalletDebit->super_wallet_id = $superWalletAccount->id;
        $superWalletDebit->account_number = $superWalletAccount->account_number;
        $superWalletDebit->amount = $debitAmount;
        $superWalletDebit->destination = $debitDestination;
        $superWalletDebit->destination_id = $destinationId;
        $superWalletDebit->save();
        self::updateWallet($userId);
        return $superWalletDebit;
    }

    /**
     * @param $userId
     * @return mixed
     */
    public static function getAccountByUserId($userId)
    {
        $wallet = SuperWalletAccount::where('user_id', $userId)->first();
        self::updateWallet($userId);
        return $wallet;
    }


    public static function updateWallet($userId)
    {
        $walletBalanceData = self::walletBalanceData($userId);
        $total_credit = SuperWalletCredit::where('user_id', $userId)->sum('amount');
        $total_debit = SuperWalletDebit::where('user_id', $userId)->sum('amount');
        if (!is_null($walletBalanceData)) {
            $walletBalanceData->total_credit = is_null($total_credit) ? 0 : $total_credit;
            $walletBalanceData->total_debit = is_null($total_debit) ? 0 : $total_debit;
            $walletBalanceData->save();
        } else {
            self::getAccountByUserId($userId);
            $walletBalanceData = self::walletBalanceData($userId);
            $walletBalanceData->total_credit = is_null($total_credit) ? 0 : $total_credit;
            $walletBalanceData->total_debit = is_null($total_debit) ? 0 : $total_debit;
            $walletBalanceData->save();
        }
    }

    public static function bookingAndCommissionExist($bookingReference, $userId)
    {
        return SuperWalletCredit::query()->where('booking_reference', $bookingReference)->where('user_id', $userId)->exists();
    }


    private static function createPromoterCommission($user_id, $commission_id)
    {

        $commissionUser = CommissionCalculator::getRateUser($user_id);
        if (isset($commissionUser))
            return;
        $commissionUser = new CommissionUser();
        $commissionUser->user_id = $user_id;
        $commissionUser->commission_id = $commission_id;
        $commissionUser->user_type = 3;
        $commissionUser->save();
    }


    public static function getUser($userId)
    {
        $user = DB::table('lp_customers')->where('user_id', $userId)->first();
        return $user;
    }

    public static function isPromoter($userId)
    {
        $user = DB::table('lp_promoters')->where('user_id', $userId)->exists();
        return $user;
    }

    public static function bookingExist($bookingReference)
    {
        return DB::table('product_booking')->where('booking_reference', $bookingReference)->exists();
    }

    public static function hasOneBooking($userId)
    {
        return DB::table('product_booking')->where('booking_status', 'open')->where('user_id', $userId)->get();
    }

    public static function getPromoter($userId)
    {
        $promoter = DB::table('lp_promoters')->where('user_id', $userId)->first();
        return $promoter;
    }

    public static function getPromoterCommission(Request $request)
    {
        $b2cCommission = DB::table('lp_promoters')
            ->join('lp_money_out', 'lp_money_out.user_id', '=', 'lp_promoters.user_id')
            ->select('first_name', 'last_name', 'phone_number', 'lp_money_out.amount', 'lp_money_out.transaction_code', 'lp_money_out.created_at AS payout_date')
            ->orderBy('lp_money_out.id', 'desc');

        if ($request->has('search_filter')) {
            $b2cCommission->where(function ($query) use ($request) {
                $query->where('phone_number', 'like', '%' . $request->input('search_filter') . '%')
                    ->orWhere('first_name', 'like', '%' . $request->input('search_filter') . '%')
                    ->orWhere('last_name', 'like', '%' . $request->input('search_filter') . '%');
            });
        }

        $b2cCommission = $b2cCommission->paginate($request->input('page_size') ?? '10', ['*'], 'page', $request->input('page_index'));
        return $b2cCommission;
    }

    public static function totalPromoterDisbursement(Request $request)
    {
        $totalCommission = DB::table('lp_promoters')->join('lp_money_out', 'lp_money_out.user_id', '=', 'lp_promoters.user_id')->sum('lp_money_out.amount');
        return $totalCommission;
    }
}
