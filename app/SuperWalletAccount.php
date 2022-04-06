<?php

namespace App;

use App\SuperWalletDebit;
use App\SuperWalletCredit;
use App\SuperWalletBalance;
use Illuminate\Database\Eloquent\Model;


class SuperWalletAccount extends Model
{
    protected $appends = ['super_wallet_balance', 'super_wallet_credit', 'super_wallet_debit'];
    //
    protected $table='lp_super_wallet_account';

    protected $hidden = array('deleted_at');



    public function superWalletBalance()
    {
        return $this->hasOne(SuperWalletBalance::class, 'super_wallet_id');
    }

    public function superWalletCredit()
    {
        return $this->hasMany(SuperWalletCredit::class, 'super_wallet_id');
    }

    public function superWalletDebit()
    {
        return $this->hasMany(SuperWalletDebit::class, 'super_wallet_id');
    }


    public function getSuperWalletBalanceAttribute()
    {
        $walletBalance = $this->superWalletBalance()->first();
        return $walletBalance ?: ['total_credit' => 0, 'total_debit' => 0];
    }

    public function getSuperWalletCreditAttribute()
    {
        $walletCredit = $this->superWalletCredit()->get();

        return $walletCredit ?: [];
    }

    public function getSuperWalletDebitAttribute()
    {
        $walletDebit = $this->superWalletDebit()->get();
        return $walletDebit ?: [];
    }
}
