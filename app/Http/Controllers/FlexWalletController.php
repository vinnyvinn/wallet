<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\WalletDebit;
use App\WalletCredit;
use App\WalletRefund;
use App\WalletAccount;
use App\WalletBalance;
use App\Helpers\AuthHelper;
use Illuminate\Http\Request;
use App\Helpers\WalletHelper;
use Illuminate\Http\Response;
use App\Helpers\InvoiceHelper;
use App\Jobs\UpdateWalletsJob;
use Illuminate\Validation\Rule;
use App\Events\WalletDebitEvent;
use App\Helpers\GenerateAccount;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Events\FlexWalletDebitEvent;
use App\Helpers\FlexWalletDebitsHelper;
use App\Traits\DataTransferTrait;
use Illuminate\Support\Facades\Validator;


class FlexWalletController extends Controller
{
    /**
     * Create a new controller instance.
     *
     */
    use DataTransferTrait;

    public function __construct()
    {
        //
    }

    //
    public function createAccount(Request $request)
    {
        $validator = Validator::make($request->all(), ['userId' => 'required|exists:lp_users,id']);

        if ($validator->fails()) {
            throw new \Illuminate\Validation\ValidationException($validator);
        } else {
            $wallentAccount = WalletAccount::where('user_id', $request->userId)->first();
            if (!is_null($wallentAccount)) {
                return json_response()->error('The user has account already')
                    ->add($wallentAccount)
                    ->setStatusCode(\Illuminate\Http\Response::HTTP_BAD_REQUEST);
            } else {
                $wallentAccount = new WalletAccount();
                $wallentAccount->user_id = $request->userId;
                $wallentAccount->account_number = GenerateAccount::getCustomerAccount();
                $wallentAccount->account_status = 'open';
                $wallentAccount->save();

                $wallet_id = WalletAccount::where('account_number', $wallentAccount->account_number)->value('id');

                WalletHelper::openAccount($request->userId, $wallentAccount->account_number, $wallet_id);
                return $wallentAccount;
            }
        }
    }

    public function creditAccount(Request $request)
    {
        $wallet = $this->walletAccount($request->userId);
        if (!isset($wallet)) {
            $this->createAccount($request);
        }
        $validator = Validator::make($request->all(), [
            'userId' => 'required|exists:lp_wallet_account,user_id',
            'moneyInId' => 'required|unique:lp_wallet_credit,money_in_id',
            'amountCredited' => 'required|numeric|min:1',
            'amountSource' => 'required'
        ]);


        if (!WalletHelper::moneyInExist($request->input('moneyInId'))) {
            return json_response()->error("Invalid money in ID")->setStatusCode(\Illuminate\Http\Response::HTTP_UNPROCESSABLE_ENTITY);
            // exit(1);
        }

        if ($validator->fails()) {
            throw new \Illuminate\Validation\ValidationException($validator);
        } else {
            return WalletHelper::creditWallet($request->userId, $request->moneyInId, $request->amountCredited, $request->amountSource, $bookingRef  = $request->input('bookingRef') ?? '');
        }
    }

    public function debitAccount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'userId' => 'required|exists:lp_wallet_account,user_id',
            'booking_reference' => 'required|exists:product_booking,booking_reference',
            'debitAmount' => 'required|numeric|min:1',
        ]);

        if ($validator->fails()) {
            throw new \Illuminate\Validation\ValidationException($validator);
        } else {
            $balance = WalletHelper::walletBalance($request->userId);
            if (floatval($balance) < floatval($request->debitAmount)) {
                return json_response()->error('Wallet balance is less than the debit amount')
                    ->setStatusCode(\Illuminate\Http\Response::HTTP_UNPROCESSABLE_ENTITY);
            } else {
                event(new WalletDebitEvent($request));
            }
        }
    }

    public function flexDebitAccount(Request $request)
    {
        /**
         * Authenticate user
         */
        try {
            $auth_helper = new AuthHelper;
            $user = $auth_helper->getAuthenticatedUser($request);
        } catch (\Exception $e) {
            return json_response()->error($e->getMessage())
                ->setStatusCode(\Illuminate\Http\Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $formatted_phone = preg_replace("/[^0-9]/", "", $request->phoneNumber);
        $phone_number = '254' . substr($formatted_phone, -9);

        $request->merge(['phoneNumber' => $phone_number]);
        $wallet_debit = new WalletDebit;
        $rules = $wallet_debit->FlexDebitValidationRules();
        $messages = $wallet_debit->FlexDebitValidationMessages();
        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            throw new \Illuminate\Validation\ValidationException($validator);
        } else {
            $customer = WalletHelper::getCustomer($phone_number);
            if (!isset($customer)) {
                return json_response()->error('Customer not found')
                    ->setStatusCode(\Illuminate\Http\Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            // check user has wallet
            $wallet = WalletHelper::checkWalletExists($customer->user_id);
            if (!isset($wallet)) {
                return json_response()->error('Customer does not have a Wallet.')
                    ->setStatusCode(\Illuminate\Http\Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            $balance = WalletHelper::walletBalance($customer->user_id);
            if (floatval($balance) < floatval($request->debitAmount)) {
                return json_response()->error('Wallet balance is less than the debit amount')
                    ->setStatusCode(\Illuminate\Http\Response::HTTP_UNPROCESSABLE_ENTITY);
            } else {
                event(new FlexWalletDebitEvent($request, $user->id, $customer->user_id));
            }
        }
    }

    public function walletAccount($userId)
    {
        return WalletAccount::where('user_id', $userId)->first();
    }

    public function allWalletAccount()
    {
        return WalletAccount::get();
    }

    public function walletBalance($userId)
    {
        return WalletBalance::where('user_id', $userId)->first();
    }

    public function walletBalanceAmount($userId)
    {
        return WalletHelper::walletBalance($userId);
    }

    public function showFlexWalletTransfers(Request $request)
    {
        $transfer_helper = new FlexWalletDebitsHelper;
        $debits = $transfer_helper->getFlexDebitDetails($request);
        return $debits;
    }

    public function walletRefund(Request $request, $user_id)
    {
        /**
         * Validate
         */
        $request->merge(['wallet_user_id' => $user_id]); // Add user_id to request parameters 
        $wallet_refund = new WalletRefund;
        $rules = $wallet_refund->validationRules();
        $messages = $wallet_refund->validationMessages();
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            throw new \Illuminate\Validation\ValidationException($validator);
        }
        /**
         * Authenticate user
         */
        try {
            $auth_helper = new AuthHelper;
            $refunder = $auth_helper->getAuthenticatedUser($request);
        } catch (\Exception $e) {
            return json_response()->error($e->getMessage())
                ->setStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $wallet_helper = new WalletHelper;
        $refund = $wallet_helper->refundFromWallet($request, $user_id, $refunder->id);
        return $refund;
    }

    public function debitRefundAccount(Request $request)
    {
        Log::info(json_encode($request->input()));
        $validator = Validator::make($request->all(), [
            'userId' => 'required|exists:lp_wallet_account,user_id',
            'booking_reference' => 'required|exists:product_booking,booking_reference',
            'debitAmount' => 'required|numeric|min:1',
        ]);
        if ($validator->fails()) {
            throw new \Illuminate\Validation\ValidationException($validator);
        } else {
            $walletAccount = WalletAccount::where('user_id', $request->userId)->first();
            if ($walletAccount) {
                $balance = $walletAccount->wallet_refund_balance;
                if (floatval($balance) >= floatval($request->debitAmount)) {
                    $booking = WalletHelper::getBooking($request->booking_reference);
                    if ($booking) {
                        $requiredApproval = (floatval($request->debitAmount) > floatval(env('MAX_REQUIRED_APPROVAL', 50000)));
                        $debitWallet = $walletAccount->walletDebit()->create([
                            'user_id' => $request->userId,
                            'amount' => $request->debitAmount,
                            'account_number' => $walletAccount->account_number,
                            'destination' => 'booking',
                            'destiation_id' => $booking->id,
                            'destination_reference' => $request->booking_reference,
                            'account_type' => 'refund',
                            'debit_status' => ($requiredApproval ? 'incomplete' : 'complete'),
                            'debit_required_approval' => $requiredApproval
                        ]);
                        if ($debitWallet && !$requiredApproval) {
                            $data = ['booking_id' => $request->booking_reference, 'payment_id' => $debitWallet->id, 'wallet_id' => $debitWallet->id, 'payment_amount' => $request->debitAmount];
                            $this->sendDataPost(env('BOOKING_PAYMENT_ENDPOINT') . 'api/booking/add/payment', $data);
                        }
                    } else {
                        Log::error("Booking could not be found :: " . json_encode($request->input()));
                    }
                } else {
                    Log::error("Insufficient balance:: Pay with Refund" . json_encode($request->input()));
                }
            }
        }
    }
    public function withdrawCashRefund(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:lp_wallet_account,user_id',
            'phone_number' => 'required',
            'with_amount' => 'required',
        ]);
        if ($validator->fails()) {
            throw new \Illuminate\Validation\ValidationException($validator);
        } else {
            $walletAccount = WalletAccount::where('user_id', $request->user_id)->first();
            if ($walletAccount) {
                $balance = $walletAccount->wallet_refund_balance;
                $fee = (doubleval(env('WALLET_TRANSACTION_FEE', 1)) / 100);
                $charges = ceil($balance * $fee);
                $maxAllowed = ($charges + doubleval($request->with_amount));
                if (floatval($balance) >= floatval($maxAllowed)) {
                    $invoice = InvoiceHelper::addInvoice($request->user_id, $request->with_amount, Carbon::now()->toDateString());
                    $requestBody = ['phone' => $request->phone_number, 'user_id' => $request->input('user_id'), 'reference' => $invoice->invoice_number, 'amount' => $invoice->amount];
                    $requiredApproval = (floatval($request->with_amount) > floatval(env('MAX_REQUIRED_APPROVAL', 50000)));
                    $debitWallet = $walletAccount->walletDebit()->create([
                        'user_id' => $request->user_id,
                        'amount' =>  $request->with_amount,
                        'account_number' => $walletAccount->account_number,
                        'destination' => 'withdrawal',
                        'destiation_id' => $invoice->id,
                        'destination_reference' => $invoice->invoice_number,
                        'debit_status' => ($requiredApproval ? 'incomplete' : 'complete'),
                        'debit_required_approval' => $requiredApproval,
                        'account_type' => 'refund',
                        'withdrawal_fee' => $charges
                    ]);
                    //New online store
                    if ($debitWallet) {
                        $result = $this->sendDataPost(env('PAYMENT_ENDPOINT') . 'api/b2c/request', $requestBody);
                        Log::notice("Sent to MPESA-B2C =" . json_encode($requestBody) . ",b2c-response=" . json_encode($result));
                    }
                } else {
                    Log::error("Insufficient balance:: Sent to MPESA-B2C =" . json_encode($request->input()));
                }
            } else {
                Log::error("Customer Invalid Wallet ID:: Sent to MPESA-B2C =" . json_encode($request->input()));
            }
        }
    }


    public function updateCredit()
    {
        WalletAccount::chunk(300, function ($wallets) {
            $wallets->map(function ($wallet) {
                $credits = WalletCredit::where('user_id', $wallet->user_id)->get();
                if (!empty($credits)) {
                    $credits->map(function ($credit) use ($wallet) {
                        $credit->wallet_id = $wallet->id;
                        $credit->account_number = $wallet->account_number;
                        $credit->save();
                    })->chunk(100);
                }
            });
        });
    }

    public function updateDebit()
    {
        WalletAccount::chunk(300, function ($wallets) {
            $wallets->map(function ($wallet) {
                $debits = WalletDebit::where('user_id', $wallet->user_id)->get();
                if (!empty($debits)) {
                    $debits->map(function ($debit) use ($wallet) {
                        $debit->wallet_id = $wallet->id;
                        $debit->account_number = $wallet->account_number;
                        $debit->save();
                    })->chunk(100);
                }
            });
        });
    }

    public function updateWalletBalance()
    {
        WalletBalance::chunk(300, function ($wallets) {
            $wallets->map(function ($wallet_balance) {
                $wallet_account = WalletAccount::where('user_id', $wallet_balance->user_id)->first();
                if (isset($wallet_account)) {
                    $wallet_balance->account_number = $wallet_account->account_number;
                    $wallet_balance->wallet_id = $wallet_account->id;
                    $wallet_balance->save();
                }
            });
        });
    }

    public function updateWalletAccounts()
    {
        WalletAccount::chunk(300, function ($wallets) {
            $wallets->map(function ($wallet) {
                // Get account of previous wallet
                $previous_wallet_id = WalletAccount::where('id', '<', $wallet->id)->max('id');
                $previous_account = WalletAccount::where('id', $previous_wallet_id)->first();

                if (!isset($previous_account)) {
                    $wallet->account_number = '100000001.00';
                } else {
                    $wallet->account_number = intval($previous_account->account_number) + '1';
                }
                $wallet->save();
            });
        });
    }
}
