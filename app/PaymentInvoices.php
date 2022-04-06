<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class PaymentInvoices extends Model
{
    protected $table = 'lp_invoice';
    protected $hidden = array('deleted_at');
}
