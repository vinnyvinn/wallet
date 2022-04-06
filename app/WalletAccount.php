<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class WalletAccount extends Model
{
    protected $appends = ['wallet_balance', 'wallet_credit', 'wallet_debit', 'wallet_refund_balance'];
    //
    protected $table = 'lp_wallet_account';

    public function walletBalance()
    {
        return $this->hasOne(WalletBalance::class, 'wallet_id');
    }

    public function walletCredit()
    {
        return $this->hasMany(WalletCredit::class, 'wallet_id');
    }

    public function walletDebit()
    {
        return $this->hasMany(WalletDebit::class, 'wallet_id');
    }


    public function getWalletBalanceAttribute()
    {
        $walletBalance = $this->walletBalance()->first();
        return $walletBalance ?: ['total_credit' => 0, 'total_debit' => 0];
    }

    public function getWalletRefundBalanceAttribute()
    {
        $totalCredit = $this->walletCredit()->where('account_type', 'refund')->sum('amount');
        $totalDebit = $this->walletDebit()->where('account_type', 'refund')->sum('amount');
        return ($totalCredit - $totalDebit);
    }
    public function getWalletCreditAttribute()
    {
        $walletCredit = $this->walletCredit()->get();

        return $walletCredit ?: [];
    }

    public function getWalletDebitAttribute()
    {
        $walletDebit = $this->walletDebit()->get();
        return $walletDebit ?: [];
    }
}
