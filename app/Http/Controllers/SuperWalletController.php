<?php

namespace App\Http\Controllers;

use App\Helpers\GenerateAccount;
use App\Helpers\InvoiceHelper;
use App\Helpers\SuperWalletHelper;
use App\SuperWalletAccount;
use App\SuperWalletBalance;
use App\Traits\DataTransferTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SuperWalletController extends Controller
{
    use DataTransferTrait;

    /**
     * Create a new controller instance.
     *
     */

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
            $superWallentAccount = SuperWalletAccount::where('user_id', $request->userId)->first();
            if (!is_null($superWallentAccount)) {
                return json_response()->error('The user has commission account already')
                    ->add($superWallentAccount)
                    ->setStatusCode(\Illuminate\Http\Response::HTTP_ALREADY_REPORTED);
            } else {
                $superWallentAccount = new SuperWalletAccount();
                $superWallentAccount->user_id = $request->userId;
                $superWallentAccount->account_number = GenerateAccount::getAccountNumber(80);
                $superWallentAccount->account_status = 'open';
                $superWallentAccount->save();
                SuperWalletHelper::openAccount($request->userId, $superWallentAccount->account_number, $superWallentAccount->id);
                return $superWallentAccount;
            }
        }
    }

    public function creditAccount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'userId' => 'required|exists:lp_super_wallet_account,user_id',
            'product_id' => 'required|numeric|exists:lp_products,id',
            'booking_id' => 'required|numeric|exists:product_booking,id',
            'booking_reference' => 'required|exists:product_booking,booking_reference',
            'amount' => 'required|numeric|min:1',
            'amountSource' => 'required'
        ]);
        if ($validator->fails()) {
            throw new \Illuminate\Validation\ValidationException($validator);
        } else {
            if (SuperWalletHelper::bookingAndCommissionExist($request->booking_reference, $request->userId) && SuperWalletHelper::isPromoter($request->userId)) {
                return json_response()->error('The commission has already been allocated!')->setStatusCode(\Illuminate\Http\Response::HTTP_ALREADY_REPORTED);
            } else {
                return SuperWalletHelper::creditWallet($request->userId, $request->product_id, $request->booking_id, $request->booking_reference, $request->amount, $request->amountSource);
            }
        }
    }

    public function debitAccount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'userId' => 'required|exists:lp_super_wallet_account,user_id',
            'debit_amount' => 'required|numeric|min:1',
            'debit_destination' => 'required',
            'destination_id' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            throw new \Illuminate\Validation\ValidationException($validator);
        } else {
            $balance = SuperWalletHelper::walletBalance($request->userId);
            if (floatval($balance) < floatval($request->debit_amount)) {
                return json_response()->error('Wallet balance is less than the debit amount')->setStatusCode(\Illuminate\Http\Response::HTTP_UNPROCESSABLE_ENTITY);
            } else {
                return SuperWalletHelper::debitWallet($request->userId, $request->debit_amount, $request->debit_destination, $request->destination_id);
            }
        }
    }

    public function requestFund(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:lp_super_wallet_account,user_id',
            'phone_number' => 'required',
            'with_amount' => 'required|min:1000',
        ]);
        if ($validator->fails()) {
            throw new \Illuminate\Validation\ValidationException($validator);
        } else {
            $balance = SuperWalletHelper::walletBalance($request->user_id);
            if (floatval($balance) >= floatval($request->with_amount)) {
                $invoice = InvoiceHelper::addInvoice($request->user_id, $request->with_amount, Carbon::now()->toDateString());
                $debitSuperWallet = SuperWalletHelper::debitWallet($request->input('user_id'), $request->with_amount, $invoice->invoice_number, $invoice->id);
            } else {
                return "Not Valid";
            }
        }
    }

    public function withdraw(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:lp_super_wallet_account,user_id',
            'phone_number' => 'required',
            'with_amount' => 'required',
        ]);
        if ($validator->fails()) {
            throw new \Illuminate\Validation\ValidationException($validator);
        } else {

            $balance = SuperWalletHelper::walletBalance($request->user_id);
            if (floatval($balance) >= floatval($request->with_amount)) {
                $invoice = InvoiceHelper::addInvoice($request->user_id, $request->with_amount, Carbon::now()->toDateString());
                $requestBody = ['phone' => $request->phone_number, 'user_id' => $request->input('user_id'), 'reference' => $invoice->invoice_number, 'amount' => $invoice->amount];
                $debitSuperWallet = SuperWalletHelper::debitWallet($request->input('user_id'), $request->with_amount, $invoice->invoice_number, $invoice->id);
                if (isset($debitSuperWallet)) {
                    $result = $this->sendDataPost(env('PAYMENT_ENDPOINT') . 'api/b2c/request', $requestBody);
                    Log::notice("Sent to MPESA-B2C =" . serialize($requestBody) . ",b2c-response=" . serialize($result));
                }
            } else {
                return "Not Valid";
            }
        }
    }

    public function walletAccount($userId)
    {
        return SuperWalletAccount::where('user_id', $userId)->first();
    }

    public function allWalletAccount()
    {
        return SuperWalletAccount::get();
    }

    public function walletBalance($userId)
    {
        return SuperWalletBalance::where('user_id', $userId)->first();
    }

    public function walletBalanceAmount($userId)
    {
        return SuperWalletHelper::walletBalance($userId);
    }

    public function promoterCommission(Request $request)
    {
        return SuperWalletHelper::getPromoterCommission($request);
    }

    public function promoterSumCommission(Request $request)
    {
        return SuperWalletHelper::totalPromoterDisbursement($request);
    }


}
