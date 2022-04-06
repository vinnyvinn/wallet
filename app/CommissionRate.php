<?php

namespace App;

use CommissionsTarrif;
use Illuminate\Database\Eloquent\Model;


class CommissionRate extends Model
{

    //
    protected $table = 'lp_commission_rate';
    protected $hidden = array('deleted_at');

    protected $fillable = ['sector_id', 'user_type', 'category_commissioned', 'commission_cost_inclusive', 'commission_type', 'applied', 'percentage_value','commission_name','tariff_type'];

    public function tariff()
    {
        return $this->hasMany(CommissionsTarrif::class, 'commission_id');
    }

    public function commissionUser()
    {
        return $this->hasOne(CommissionUser::class, 'lp_user_commission');
    }
}
