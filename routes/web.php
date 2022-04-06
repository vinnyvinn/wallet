<?php


/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
 */

$router->group(['prefix' => 'api', 'middleware' => ['ipcheck']], function () use ($router) {
    //Customer Wallet Account
    $router->get('flexpay/wallet', ['uses' => 'FlexWalletController@allWalletAccount']);
    $router->get('flexpay/wallet/{user_id}', 'FlexWalletController@walletAccount');
    $router->get('flexpay/wallet/balance/{user_id}', 'FlexWalletController@walletBalance');
    $router->get('flexpay/wallet/balance/amount/{user_id}', 'FlexWalletController@walletBalanceAmount');
    $router->post('flexpay/wallet/create', 'FlexWalletController@createAccount');
    $router->post('flexpay/wallet/credit', 'FlexWalletController@creditAccount');
    $router->post('flexpay/wallet/debit', 'FlexWalletController@debitAccount');
    $router->post('flexpay/wallet/flexpay_debit', 'FlexWalletController@flexDebitAccount');
    $router->post('flexpay/wallet/delete', 'FlexWalletController@delete');
    $router->put('flexpay/wallet/update/{id}', 'FlexWalletController@update');
    $router->post('flexpay/wallet/account_updates', 'FlexWalletController@updateWalletAccounts');
    $router->post('flexpay/wallet/credit_updates', 'FlexWalletController@updateCredit');
    $router->post('flexpay/wallet/debit_updates', 'FlexWalletController@updateDebit');
    $router->post('flexpay/wallet/balance_updates', 'FlexWalletController@updateWalletBalance');
    $router->post('flexpay/wallet/flexpay_transfers', 'FlexWalletController@showFlexWalletTransfers');
    
    $router->post('flexpay/wallet/{id}/refund', 'FlexWalletController@walletRefund');
    $router->post('flexpay/customer/refund/cash', 'FlexWalletController@withdrawCashRefund');
    $router->post('flexpay/wallet/refund/debit', 'FlexWalletController@debitRefundAccount');


    //SuperWallet Account
    $router->post('flexpay/commission/create', 'SuperWalletController@createAccount');
    $router->get('flexpay/commission', ['uses' => 'SuperWalletController@allWalletAccount']);
    $router->get('flexpay/commission/{user_id}', 'SuperWalletController@walletAccount');
    $router->get('flexpay/commission/balance/{user_id}', 'SuperWalletController@walletBalance');
    $router->get('flexpay/commission/balance/amount/{user_id}', 'SuperWalletController@walletBalanceAmount');
    $router->post('flexpay/commission/credit', 'SuperWalletController@creditAccount');
    $router->post('flexpay/commission/debit', 'SuperWalletController@debitAccount');
    $router->post('flexpay/commission/delete', 'SuperWalletController@delete');
    $router->put('flexpay/commission/update/{id}', 'SuperWalletController@update');
    $router->post('flexpay/commission/withdraw', 'SuperWalletController@withdraw');

    //SuperWallet Commission
    $router->post('flexpay/rate/create', 'FlexCommissionController@createRate');
    $router->get('flexpay/rate/show/{sector_id}', 'FlexCommissionController@showRate');
    $router->post('flexpay/rate/update/{commission_id}', 'FlexCommissionController@updateRate');
    $router->post('flexpay/rate/delete/{commission_rate_id}', 'FlexCommissionController@deleteRate');

    //SuperWallet Tariff
    $router->post('flexpay/tariff/create', 'FlexTariffController@createTariff');
    $router->get('flexpay/tariff/show/{sector_id}', 'FlexTariffController@showTariff');
    $router->post('flexpay/tariff/update/{tariff_id}', 'FlexTariffController@updateTariff');
    $router->post('flexpay/tariff/delete/{tariff_id}', 'FlexTariffController@deleteTariff');

    //SuperWallet Commission User 
    $router->post('flexpay/rate/user/create', 'FlexUserCommissionController@createCommissionUser');
    $router->get('flexpay/rate/user/show/{commission_user_id}', 'FlexUserCommissionController@showCommissionUser');
    $router->post('flexpay/rate/user/update/{commission_user_id}', 'FlexUserCommissionController@updateCommissionUser');
    $router->post('flexpay/rate/user/delete/{commission_user_id}', 'FlexUserCommissionController@deleteCommissionUser');

    //SuperWallet Invoices
    $router->post('flexpay/payment/request', 'SuperWalletController@requestFund');
    $router->get('flexpay/payment/invoice/{id}', 'InvoiceController@invoice');
    $router->get('flexpay/payment/invoices/{user_id}', 'InvoiceController@invoiceList');
    $router->post('flexpay/payment/commission/promoters', 'SuperWalletController@promoterCommission');
    $router->get('flexpay/payment/commission/promoters/total', 'SuperWalletController@promoterSumCommission');
});
