<?php

namespace App\SearchFilters;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Query\Builder;

class DateFilter
{
    public function filter(Request $request, Builder $wallets) {
        $wallets = $wallets->where(function ($query) use ($request) {
            $dates = explode(' ', $request->input('date'));
            $query->whereDate('lp_wallet_debit.created_at', '>=', date($dates[0]))
                ->whereDate('lp_wallet_debit.created_at', '<=', date($dates[1]));
        });
        return $wallets;
    }

}