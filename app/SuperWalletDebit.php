<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SuperWalletDebit extends Model
{
    //
    protected $table='lp_super_wallet_debit';
    protected $hidden = array('deleted_at');
}
