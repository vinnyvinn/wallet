<?php

namespace App\Listeners;

use App\Events\WalletEvent;
use App\Helpers\WalletHelper;
use App\Traits\DataTransferTrait;
use Illuminate\Support\Facades\Log;

class WalletListener
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
     * @param  WalletEvent $event
     * @return void
     */
    public function handle(WalletEvent $event)
    {
        $walletBalance = WalletHelper::walletBalance($event->walletAccount->user_id);
        if (!empty($event->bookingReference)) {
            $booking = WalletHelper::hasOneBooking($event->walletAccount->user_id);
            if (WalletHelper::bookingExist($event->bookingReference)) {
                $debitWallet = WalletHelper::debitWallet($event->walletAccount->user_id, $event->walletAccount->id, $event->walletAccount->amount, "booking", $event->bookingReference);
                $data = ['booking_id' => $event->bookingReference, 'payment_id' => $debitWallet->id, 'wallet_id' => $debitWallet->id, 'payment_amount' => $debitWallet->amount];
                $this->sendDataPost(env('BOOKING_PAYMENT_ENDPOINT') . 'api/booking/add/payment', $data);
                Log::notice("BookingPayment  " . json_encode($data));
            } elseif ($booking->count() == 1) {
                $debitWallet = WalletHelper::debitWallet($event->walletAccount->user_id, $event->walletAccount->id, $event->walletAccount->amount, "booking", $booking[0]->booking_reference);
                $data = ['booking_id' => $booking[0]->booking_reference, 'payment_id' => $event->walletAccount->money_in_id, 'wallet_id' => $debitWallet->id, 'payment_amount' => $debitWallet->amount];
                $this->sendDataPost(env('BOOKING_PAYMENT_ENDPOINT') . 'api/booking/add/payment', $data);
                Log::notice("BookingPaymentLNM  " . json_encode($data));
            } else {
                $user = WalletHelper::getUser($event->walletAccount->user_id);
                $data = [
                    'recipients' => $user->phone_number_1,
                    'template_id' => '14',
                    'first_name' => $user->first_name,
                    'amount_paid' => $event->walletAccount->amount,
                    'phone_no' => '0719725060',
                    'date' => (string)$event->walletAccount->created_at,
                    'balance' => $walletBalance];
                $this->sendDataPost(env('SMS_ENDPOINT') . 'api/send_sms', $data);

            }
        } else {
            //SEND WALLET TOP UP
            $user = WalletHelper::getUser($event->walletAccount->user_id);
            $data = [
                'recipients' => $user->phone_number_1,
                'template_id' => '14',
                'first_name' => $user->first_name,
                'amount_paid' => $event->walletAccount->amount,
                'phone_no' => '0719725060',
                'date' => (string)$event->walletAccount->created_at,
                'balance' => $walletBalance];
            $this->sendDataPost(env('SMS_ENDPOINT') . 'api/send_sms', $data);

        }
    }
}
