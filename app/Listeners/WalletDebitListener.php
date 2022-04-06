<?php

namespace App\Listeners;

use App\Events\WalletDebitEvent;
use App\Events\WalletEvent;
use App\Helpers\WalletHelper;
use App\Traits\DataTransferTrait;
use Illuminate\Support\Facades\Log;

class WalletDebitListener
{
    use DataTransferTrait;

    /**
     * Create the event listener.
     *
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param WalletDebitEvent|WalletEvent $event
     * @return void
     */
    public function handle(WalletDebitEvent $event)
    {
        $walletBalance = WalletHelper::getAccountByUserId($event->walletRequest->userId);
        if (isset($event->walletRequest->booking_reference) && !empty($event->walletRequest->booking_reference) && WalletHelper::bookingExist($event->walletRequest->booking_reference)) {
            $walletDebit = WalletHelper::debitWallet($event->walletRequest->userId, $walletBalance->id, $event->walletRequest->debitAmount, "Booking", $event->walletRequest->booking_reference);
            $data = ['booking_id' => $event->walletRequest->booking_reference, 'payment_id' => $walletDebit->id, 'wallet_id' => $walletDebit->id, 'payment_amount' => $walletDebit->amount];
            $this->sendDataPost(env('BOOKING_PAYMENT_ENDPOINT') . 'api/booking/add/payment', $data);
        } else {
            Log::info("No Direction" . json_encode($event->walletRequest));
        }
    }
}
