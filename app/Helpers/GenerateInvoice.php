<?php

namespace App\Helpers;

use App\PaymentInvoices;

class GenerateInvoice
{

    public static function getInvoice($user_id)
    {
        $invoice = PaymentInvoices::whereYear('created_at', '=', date('Y'))->get();
        $number = !is_null($invoice) ? $invoice->count() : 0;
        return $invoice = 'INV' . $user_id . date('Y') . sprintf("%'.05d", ($number + 1));
    }
}
