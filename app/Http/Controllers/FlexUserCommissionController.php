<?php

namespace App\Http\Controllers;

use App\CommissionUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FlexUserCommissionController extends Controller
{
    /**
     * Create a new controller instance.
     *
     */

    public function __construct()
    {
    }

    public function createCommissionUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|numeric|exists:lp_users,id',
            'commission_id' => 'required|numeric|exists:lp_commission_rate,id',
            'merchant_id' => 'required|exists:lp_merchants,id',
            'user_type' => 'required|exists:lp_user_types,id',
        ]);
        if ($validator->fails()) {
            throw new \Illuminate\Validation\ValidationException($validator);
        } else {
            $cRate = CommissionUser::create([
                'user_id' => $request->input('user_id'),
                'user_type' => $request->input('user_type'),
                'merchant_id' => $request->input('merchant_id'),
                'commission_id' => $request->input('commission_id')
            ]);
            return $cRate;
        }
    }

    public function showCommissionUser($commission_user_id)
    {
        $cuRate = CommissionUser::where('id', $commission_user_id)->first();

        if (isset($cuRate)) {
            return $cuRate;
        } else {
            return json_response()->error('Commission User not found')
                ->setStatusCode(\Illuminate\Http\Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function updateCommissionUser($commission_user_id, Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'user_id' => 'required|numeric|exists:lp_users,id',
                'commission_id' => 'required|numeric|exists:lp_commission_rate,id',
                'merchant_id' => 'required|exists:lp_merchant,id',
                'user_type' => 'required|exists:lp_user_types,id',
            ]
        );
        $comm_user = CommissionUser::find($commission_user_id);

        if (isset($comm_user)) {
            if ($validator->fails()) {
                throw new \Illuminate\Validation\ValidationException($validator);
            } else {
                CommissionUser::where('id', $commission_user_id)->update([
                    'user_id' => $request->input('user_id'),
                    'user_type' => $request->input('user_type'),
                    'merchant_id' => $request->input('merchant_id'),
                    'commission_id' => $request->input('commission_id'),
                ]);

                return json_response()->add('The Commission User was updated successfully')->setStatusCode(\Illuminate\Http\Response::HTTP_OK);
            }
        } else {
            return json_response()->error('Commission User not found')
                ->setStatusCode(\Illuminate\Http\Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function deleteCommissionUser($commission_user_id)
    {
        $commission = CommissionUser::find($commission_user_id);
        if (!isset($commission)) {
            return json_response()->error('The commission user is not set correctly')->setStatusCode(\Illuminate\Http\Response::HTTP_BAD_REQUEST);
        } else {
            $commission->delete();
            return json_response()->add('The commission user was deleted successfully')->setStatusCode(\Illuminate\Http\Response::HTTP_OK);
        }
    }
}
