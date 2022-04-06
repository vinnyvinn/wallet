<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class CommissionUser extends Model
{

    //
    protected $table = 'lp_user_commission';
    protected $hidden = array('deleted_at');

    protected $fillable = ['user_id', 'commission_id', 'merchant_id', 'user_type'];

    public function commission()
    {
        return $this->hasOne(CommissionRate::class);
    }
}
