<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SuperWalletBalance extends Model
{
    //
    protected $table='lp_super_wallet_balance';

    protected $hidden = array('deleted_at');
    
}
