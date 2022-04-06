<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WalletDebit extends Model
{
    //
    protected $table = 'lp_wallet_debit';

    protected  $fillable = [
        'wallet_id',
        'destination_id',
        'money_out_id',
        'user_id', 'account_number', 'amount',
        'destination', 'destination_reference',
        'debit_status',
        'account_type',
        'debit_required_approval',
        'withdrawal_fee'
    ];

    public function FlexDebitValidationRules()
    {
        $rules = [
            'phoneNumber' => 'required|exists:lp_customers,phone_number_1',
            'booking_reference' => 'required|exists:product_booking,booking_reference',
            'debitAmount' => 'required|numeric|min:1',
        ];
        return $rules;
    }

    public function FlexDebitValidationMessages()
    {
        $messages = [
            'phoneNumber.required' => 'Phone number is required',
            'phoneNumber.exists' => 'Phone number does not exist',
            'booking_reference.required' => 'Booking reference is required',
            'booking_reference.exists' => 'Booking reference does not exist',
            'debitAmount.required' => 'Debit amount is required',
            'debitAmount.numeric' => 'Debit amount should be a number',
            'debitAmount.min' => 'Debit amount should be more.',
        ];
        return $messages;
    }
}
