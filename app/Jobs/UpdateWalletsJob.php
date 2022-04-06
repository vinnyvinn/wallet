<?php

namespace App\Jobs;

use App\WalletDebit;
use App\WalletCredit;
use App\WalletAccount;
use App\WalletBalance;

class UpdateWalletsJob extends Job
{
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        WalletAccount::chunk(2000, function ($wallets) {
            $wallets->map(function ($wallet) {
                $wallet_account = WalletAccount::where('user_id', $wallet->user_id)->first();
                if (isset($wallet_account)) {
                    // Get account of previous wallet
                    $previous_wallet_id = WalletAccount::where('id', '<', $wallet_account->id)->max('id');
                    $previous_account = WalletAccount::where('id', $previous_wallet_id)->first();

                    if (!isset($previous_account)) {
                        $wallet_account->account_number = '100000001.00';
                        $wallet_account->save();
                    } else {
                        $wallet_account->account_number = intval($previous_account->account_number) + 1;
                        $wallet_account->save();
                    }

                    $wallet_account = WalletAccount::where('user_id', $wallet->user_id)->first();  // Retrieve updated model
                    
                    $credits = WalletCredit::where('user_id', $wallet_account->user_id)->get();
                    if (!empty($credits)) {
                        $credits->map(function ($credit) use($wallet_account){
                            $credit->wallet_id = $wallet_account->id;
                            $credit->account_number = $wallet_account->account_number;
                            $credit->save();
                        });
                    }

                    $debits = WalletDebit::where('user_id', $wallet_account->user_id)->get();
                    if (!empty($debits)) {
                        $debits->map(function ($debit) use($wallet_account){
                            $debit->wallet_id = $wallet_account->id;
                            $debit->account_number = $wallet_account->account_number;
                            $debit->save();
                        });
                    }

                    $wallet_balance = WalletBalance::where('user_id', $wallet_account->user_id)->first();
                    if (isset($wallet_balance)) {
                        $wallet_balance->account_number = $wallet_account->account_number;
                        $wallet_balance->wallet_id= $wallet_account->id;
                        $wallet_balance->save();
                    }
                    
                }
                

            });
        });
    }
}
