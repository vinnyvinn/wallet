<?php
namespace App\Http\Controllers;

use App\CommissionRate;
use App\CommissionTariff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FlexTariffController extends Controller
{
    /**
     * Create a new controller instance.
     *
     */

    public function __construct()
    {
        //
    }
    //
    public function createTariff(Request $request)
    {
       
        $validator = Validator::make($request->all(), [
            'commission_id' => 'required|exists:lp_commission_rate,id',
            'min_value' => 'required',
            'tariff_name'=>'required',
            'max_value' => 'required',
            'commission_tariff_value' => 'required'
        ]);

        if ($validator->fails()) {
            throw new \Illuminate\Validation\ValidationException($validator);
        } else {
            $tariff = CommissionTariff::create([
                'commission_id' => $request->input('commission_id'),
                'tariff_name' => $request->input('tariff_name'),
                'min_value' => $request->input('min_value'),
                'max_value' => $request->input('max_value'),
                'commission_tariff_value' => $request->input('commission_tariff_value')
            ]);
            return $tariff;
        }
    }
    public function showTariff($sector)
    {
        $tariffs = CommissionTariff::with(['rate' => function ($query) use ($sector){
            $query->where('sector_id', $sector);
        }])->get();
        if ($tariffs->isEmpty()) {            
            return json_response()->error('Tariff not found')
            ->setStatusCode(\Illuminate\Http\Response::HTTP_UNPROCESSABLE_ENTITY);
        } else {
            foreach ($tariffs as $tariff) {
                if (!isset($tariff->rate)) {
                    return json_response()->error('Rates not found')
                    ->setStatusCode(\Illuminate\Http\Response::HTTP_UNPROCESSABLE_ENTITY);
                }
            }
            return $tariffs;
        }  
    }

    public function updateTariff($tariff_id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'commission_id' => 'required',
            'tariff_name'=>'required',
            'min_value' => 'required',
            'max_value' => 'required',
            'commission_tariff_value' => 'required'
        ]);

        $tariff = CommissionTariff::find($tariff_id);

        if (isset($tariff)) {
            if ($validator->fails()) {
                throw new \Illuminate\Validation\ValidationException($validator);
            } else {
                $tariff = CommissionTariff::where('id', $tariff_id)->update([
                    'commission_id' => $request->input('commission_id'),
                    'tariff_name' => $request->input('tariff_name'),
                    'min_value' => $request->input('min_value'),
                    'max_value' => $request->input('max_value'),
                    'commission_tariff_value' => $request->input('commission_tariff_value')
                ]);
                return json_response()->add('The Tariff was updated successfully')->setStatusCode(\Illuminate\Http\Response::HTTP_OK);
            }
        } else {
            return json_response()->error('Tariff not found')
            ->setStatusCode(\Illuminate\Http\Response::HTTP_UNPROCESSABLE_ENTITY);
        }  
    }

    public function deleteTariff($tariff)
    {
        $tariff = CommissionTariff::find($tariff);
        
        if (!isset($tariff)) {
            return json_response()->error('The Tariff ID is no set correctly')->setStatusCode(\Illuminate\Http\Response::HTTP_BAD_REQUEST);
        } else {
            $tariff->delete();
            return json_response()->add('The Tariff was deleted successfully')->setStatusCode(\Illuminate\Http\Response::HTTP_OK);
        }
    }
}
