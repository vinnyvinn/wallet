<?php

namespace App\Helpers;

use App\CommissionRate;
use App\CommissionTariff;
use App\CommissionUser;

class CommissionCalculator
{

    public function __construct()
    {

    }

    public static function getCommissionRate($userId, $amountToCommission): float
    {
        $rateUser = self::getRateUser($userId);
        $commission = 0.0;
        $commissionRate = CommissionRate::query()->where('id', optional($rateUser)->commission_id)->first();
        if (!is_null($commissionRate)) {
            if ($commissionRate->commssion_type === 'onPercentage') {
                $commission = (($amountToCommission * floatval($commissionRate->percentage_value)) / 100);
            } else {
                $commission = self::getTariff($commissionRate->id, $amountToCommission);
            }
        }
        return $commission;
    }

    static function getTariff($commission_id, $transactionAmount): float
    {
        $tariff = CommissionTariff::query()->where('min_value', '<=', $transactionAmount)->where('max_value', '>=', $transactionAmount)->where('commission_id', $commission_id)->first();
        if (!is_null($tariff)) {
            return $tariff->commission_tariff_value;
        } else {
            return 0.0;
        }
    }

    public static function getRateUser($userId)
    {
        return CommissionUser::query()->where('user_id', $userId)->first();

    }
}
