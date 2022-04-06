<?php

namespace App\Events;


class FlexWalletDebitEvent extends Event
{
    public $walletRequest;
    public $user_id;

    /**
     * Create a new event instance
     * @param $walletRequest
     */
    public function __construct($walletRequest, $user_id, $customer_user_id)
    {
        $this->walletRequest = $walletRequest;
        $this->user_id = $user_id;
        $this->customer_user_id = $customer_user_id;
    }
}
