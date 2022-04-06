<?php

namespace App\Helpers;

use Illuminate\Http\Request;
use App\SearchFilters\DateFilter;
use Illuminate\Support\Facades\DB;
use App\SearchFilters\WalleTransfertFilters;


class FlexWalletDebitsHelper
{
    public function getFlexDebits()
    {
        $debits = DB::table('lp_wallet_transfers')
            ->join('lp_wallet_debit', 'lp_wallet_debit.id', '=', 'lp_wallet_transfers.wallet_debit_id')
            ->join('product_booking', 'lp_wallet_transfers.booking_id', '=', 'product_booking.id')
            ->join('lp_customers', 'lp_customers.user_id', '=', 'lp_wallet_transfers.wallet_user_id')
            ->leftJoin('lp_flexpay_users', 'lp_wallet_transfers.transferred_by', '=', 'lp_flexpay_users.user_id')
            ->select(DB::raw("CONCAT(lp_flexpay_users.first_name, ' ', lp_flexpay_users.last_name)as transferred_by"),
            'lp_wallet_debit.amount', 'lp_wallet_debit.created_at as transfer_time',
            DB::raw("CONCAT(lp_customers.first_name, ' ', lp_customers.last_name) as customer_name"),
            'lp_customers.phone_number_1 as customer_phone',
            'product_booking.booking_reference', 
            'product_booking.user_id as booking_user_id')
            ->orderBy('lp_wallet_transfers.id', 'desc');
        return $debits;
    }

    public function getFlexDebitDetails(Request $request)
    {
        $debits = $this->getFlexDebits();
        if ($request->filled('search_filter')) {
            $debit_filters = new WalleTransfertFilters;
            $debits = $debit_filters->filter($request, $debits);
        }

        if ($request->filled('date')) {
            $date_filters = new DateFilter;
            $debits = $date_filters->filter($request, $debits);
        }

        $debits = $debits->paginate($request->input('page_size') ?? '10', ['*'], 'page', $request->input('page_index'));
        
        if ($debits->isNotEmpty()) {
            foreach ($debits as $debit) {
                $recipient = $this->getDebitReceiver($debit->booking_user_id);
                if (isset($recipient)) {
                    $debit->recipient = $recipient;
                }
            }
        }
        return $debits;
    }

    public function getDebitReceiver($user_id)
    {
        $customers = DB::table('lp_customers')
            ->select(DB::raw('CONCAT(first_name, " ", last_name) as recipient_name'),
            'phone_number_1 as recipient_phone')
            ->where('user_id', $user_id)
            ->first();
        return $customers;
    }

}