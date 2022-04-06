<?php
/**
 * Created by PhpStorm.
 * User: mosesgathecha
 * Date: 23/11/2018
 * Time: 03:38
 */

namespace App\Helpers;


use App\PaymentInvoices;

class InvoiceHelper
{


    public static function addInvoice($userId, $invoiceAmount, $dueDate)
    {
        $invoice = new PaymentInvoices();
        $invoice->user_id = $userId;
        $invoice->invoice_number = GenerateInvoice::getInvoice($userId);
        $invoice->amount = $invoiceAmount;
        $invoice->account_number = $userId;
        $invoice->due_date = $dueDate;
        $invoice->invoice_status = 'processing';
        $invoice->save();
        return $invoice;
    }

    public static function getInvoice($id)
    {
        $invoice = PaymentInvoices::find($id);
        if (isset($invoice)) {
            return $invoice;
        } else {
            return json_response()->error('Payment invoice not found')
            ->setStatusCode(\Illuminate\Http\Response::HTTP_UNPROCESSABLE_ENTITY);
        }   
    }

    public static function getInvoices($user_id)
    {
        $invoice = PaymentInvoices::where('user_id', $user_id)->first();
        if (isset($invoice)) {
            return $invoice;
        } else {
            return json_response()->error('Payment invoice not found')
            ->setStatusCode(\Illuminate\Http\Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

}