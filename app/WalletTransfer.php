<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WalletTransfer extends Model
{
    //
    protected $table='lp_wallet_transfers';
    protected $fillable = ['wallet_user_id','amount','booking_id','transferred_by'];
}
