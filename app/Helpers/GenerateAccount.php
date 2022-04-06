<?php

namespace App\Helpers;

use App\SuperWalletAccount;
use App\WalletAccount;

class GenerateAccount
{

    public static function getCustomerAccount()
    {
        $account = WalletAccount::orderBy('id','desc')->first();
        return intval($account->account_number) + 1;
        // $number = !is_null($account) ? substr($account->account_number, 6) : '0';
        // return '1' . sprintf("%'.08d\n", ($number + 1));
    }

    public static function getAccountNumber($prefix = '')
    {
        $account = SuperWalletAccount::whereYear('created_at', '=', date('Y'))->orderBy('id', 'DESC')->first();
        $number = !is_null($account) ? substr($account->account_number, 6) : '0';
        return $prefix . '1' . sprintf("%'.08d\n", ($number + 1));
    }
}
