<?php

namespace App\Listeners;

use App\WalletTransfer;
use App\Events\WalletEvent;
use App\Helpers\WalletHelper;
use App\Traits\DataTransferTrait;
use Illuminate\Support\Facades\Log;
use App\Events\FlexWalletDebitEvent;

class FlexWalletDebitListener
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
     * @param FlexWalletDebitEvent $event
     * @return void
     */
    public function handle(FlexWalletDebitEvent $event)
    {
        $walletBalance = WalletHelper::getAccountByUserId($event->customer_user_id);
        if (isset($event->walletRequest->booking_reference) && !empty($event->walletRequest->booking_reference) && WalletHelper::bookingExist($event->walletRequest->booking_reference)) {
            $walletDebit = WalletHelper::debitWallet($event->customer_user_id, $walletBalance->id, $event->walletRequest->debitAmount, "Booking",$event->walletRequest->booking_reference);
            $data = ['booking_id' => $event->walletRequest->booking_reference, 'payment_id' =>$walletDebit->id, 'wallet_id' => $walletDebit->id, 'payment_amount' => $walletDebit->amount];
            $this->guzzlePostRequest(env('BOOKING_PAYMENT_ENDPOINT') . 'api/booking/add/payment', $data);
            $booking = WalletHelper::getBooking($event->walletRequest->booking_reference);
            $this->saveTransferDetails($walletDebit->user_id,$walletDebit->amount,$booking->id,$event->user_id, $walletDebit->id);
        } else {
            Log::info("FlexWalletDebitEvent FAILED" . json_encode($event->walletRequest));

        }
    }

    public function saveTransferDetails($wallet_user, $amount, $booking_id, $user, $debit_id)
    {
        $wallet_transfer = new WalletTransfer;
        $wallet_transfer->wallet_user_id = $wallet_user;
        $wallet_transfer->amount = $amount;
        $wallet_transfer->booking_id = $booking_id;
        $wallet_transfer->transferred_by = $user;
        $wallet_transfer->wallet_debit_id = $debit_id;
        $wallet_transfer->save();
    }
}
