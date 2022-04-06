<?php

namespace App\SearchFilters;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Query\Builder;

class WalleTransfertFilters
{
    public function filter(Request $request, Builder $wallets) {
        $wallets = $wallets->where(function ($query) use ($request) {
            $query->where('product_booking.booking_reference', 'like', '%'.$request->input('search_filter').'%')
                ->orwhere('lp_customers.phone_number_1', 'like', '%'.$request->input('search_filter').'%');
        });
        return $wallets;
    }

}