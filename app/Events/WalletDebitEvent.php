<?php

namespace App\Events;


use App\WalletCredit;

class WalletDebitEvent extends Event
{
    public $walletRequest;


    /**
     * Create a new event instance
     * @param $walletRequest
     */
    public function __construct($walletRequest)
    {
        $this->walletRequest = $walletRequest;
    }
}
