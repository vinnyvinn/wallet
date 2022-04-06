<?php

namespace App\Http\Controllers;

use App\CommissionRate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FlexCommissionController extends Controller
{
    /**
     * Create a new controller instance.
     *
     */

    public function __construct()
    {

    }

    public function createRate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'commission_name' => 'required',
            'sector_id' => 'required|numeric',
            'user_type' => 'required',
            'category_commissioned' => 'required',
            'commission_type' => 'required',
            'commission_cost_inclusive' => 'required',
            'commission_occurrence' => 'required',
            'applied' => 'required',
            'tariff_type' => 'required',
            'percentage_value' => 'required'
        ]);

        if ($validator->fails()) {
            throw new \Illuminate\Validation\ValidationException($validator);
        } else {
            $cRate = CommissionRate::create([
                'commission_name' => $request->input('commission_name'),
                'sector_id' => $request->input('sector_id'),
                'user_type' => $request->input('user_type'),
                'category_commissioned' => $request->input('category_commissioned'),
                'commission_type' => $request->input('commission_type'),
                'commission_cost_inclusive' => $request->input('commission_cost_inclusive'),
                'commission_occurrence' => $request->input('commission_occurrence'),
                'applied' => $request->input('applied'),
                'tariff_type' => $request->input('tariff_type'),
                'percentage_value' => $request->input('percentage_value')
            ]);
            return $cRate;
        }
    }

    public function showRate($sector)
    {
        $cRate = CommissionRate::query();
        if (isset($sector)) {
            $cRate->where('sector_id', $sector);
        }
        return $cRate->get();
    }

    public function updateRate($commission_id, Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'commission_name' => 'required',
                'sector_id' => 'required|numeric',
                'user_type' => 'required',
                'category_commissioned' => 'required',
                'commission_type' => 'required',
                'tariff_type' => $request->input('tariff_type'),
                'commission_cost_inclusive' => 'required',
                'commission_occurrence' => 'required',
                'applied' => 'required',
                'percentage_value' => 'required',
            ]
        );

        $commission_rate = CommissionRate::find($commission_id);
        if (!isset($commission_rate)) {
            return json_response()->error('The commission rate is not found')->setStatusCode(\Illuminate\Http\Response::HTTP_BAD_REQUEST);
        } else {
            if ($validator->fails()) {
                throw new \Illuminate\Validation\ValidationException($validator);
            } else {
                CommissionRate::where('id', $commission_id)->update([
                    'commission_name' => $request->input('commission_name'),
                    'sector_id' => $request->input('sector_id'),
                    'user_type' => $request->input('user_type'),
                    'category_commissioned' => $request->input('category_commissioned'),
                    'commission_type' => $request->input('commission_type'),
                    'commission_cost_inclusive' => $request->input('commission_cost_inclusive'),
                    'commission_occurrence' => $request->input('commission_occurrence'),
                    'applied' => $request->input('applied'),
                    'tariff_type' => $request->input('tariff_type'),
                    'percentage_value' => $request->input('percentage_value')
                ]);
                return json_response()->add('The Commission was updated successfully')->setStatusCode(\Illuminate\Http\Response::HTTP_OK);
            }
        }
    }

    public function deleteRate($rate)
    {
        $commission_rate = CommissionRate::find($rate);
        if (!isset($commission_rate)) {
            return json_response()->error('The rate ID is no set correctly')->setStatusCode(\Illuminate\Http\Response::HTTP_BAD_REQUEST);
        } else {
            $commission_rate->delete();
            return json_response()->add('The rate was deleted successfully')->setStatusCode(\Illuminate\Http\Response::HTTP_OK);
        }
    }
}
