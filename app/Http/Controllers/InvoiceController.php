<?php

namespace App\Http\Controllers;


use App\Helpers\InvoiceHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * @package Samerior\MobileMoney\Http\Controllers
 */
class InvoiceController extends Controller
{


    public function __construct()
    {

    }

    public function initiateInvoice(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|numeric|exists:lp_users,id',
            'amount' => 'required|numeric',
            'due_date' => 'required|date_format:Y-m-d',
        ]);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        } else {
            return InvoiceHelper::addInvoice($request->user_id, $request->amount, $request->due_date);
        }
    }

    public function invoice($id)
    {
        return InvoiceHelper::getInvoice($id);
    }

    public function invoiceList($user_id)
    {
        return InvoiceHelper::getInvoices($user_id);
    }


}
