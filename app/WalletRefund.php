<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WalletRefund extends Model
{
    //
    protected $table='lp_wallet_refunds';
    protected $fillable = ['wallet_user_id','refund_amount','refunded_by'];
    public $timestamps = true;
    
    public function validationRules()
    {
        $rules = [
            'wallet_user_id' => 'required|exists:lp_wallet_account,user_id',
            'refund_amount' => 'required|numeric|min:1',
        ];
        return $rules;
    }

    public function validationMessages()
    {
        $messages = [
            'wallet_user_id.required' => 'Wallet user is required',
            'wallet_user_id.exists' => 'Wallet does not exist',
            'refund_amount.required' => 'Refund amount is required',
            'refund_amount.numeric' => 'Refund amount should be a number',
            'refund_amount.min' => 'Refund amount should be at least 1',
        ];
        return $messages;
    }
}
