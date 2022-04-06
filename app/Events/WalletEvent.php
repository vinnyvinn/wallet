<?php

namespace App\Events;


use App\WalletCredit;

class WalletEvent extends Event
{
    public $walletAccount;
    public $lastAction;
    public $bookingReference;

    /**
     * Create a new event instance.
     * @param WalletCredit $walletAccount
     * @param $lastAction
     * @param string $bookingReference
     */
    public function __construct(WalletCredit $walletAccount, $lastAction, $bookingReference = '')
    {
        $this->walletAccount = $walletAccount;
        $this->lastAction = $lastAction;
        $this->bookingReference = $bookingReference;
    }
}
