<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class CommissionTariff extends Model
{
    
    //
    protected $table='lp_commission_tariff';
    protected $hidden = array('deleted_at');
    protected $fillable = ['commission_id','min_value', 'max_value','commission_tariff_value'];
    
    public function rate(){
        return $this->belongsTo(CommissionRate::class,'commission_id');
    }

}
