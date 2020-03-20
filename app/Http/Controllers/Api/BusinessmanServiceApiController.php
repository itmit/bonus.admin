<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ServiceType;
use App\Models\ServiceItem;
use App\Models\ClientCustomer;
use App\Models\ClientBusinessman;
use App\Models\ClientBalance;
use App\Models\Client;
use App\Models\BusinessmanService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class BusinessmanServiceApiController extends ApiBaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->sendResponse(BusinessmanService::join('service_items', 'businessman_services.service_item_id', '=', 'service_items.id')
        ->where('businessman_services.businessmen_id', auth('api')->user()->id)
        ->select('businessman_services.uuid', 'service_items.name', 'businessman_services.accrual_method', 'businessman_services.accrual_value', 'businessman_services.writeoff_value', 'businessman_services.writeoff_method')
        ->get()
        ->toArray(),'Список созданных услуг');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [ 
            'uuid' => 'required|uuid|exists:service_items',
            'accrual_method' => [
                'required',
                Rule::in(['points', 'percent']), // предприниматель, покупатель
            ],
            'writeoff_method' => [
                'required',
                Rule::in(['points', 'percent']), // предприниматель, покупатель
            ],
            'accrual_value' => 'required|integer',
            'writeoff_value' => 'required|integer'
        ]);

        if ($validator->fails()) { 
            return response()->json(['errors'=>$validator->errors()], 400);            
        }

        $serviceItem = ServiceItem::where('uuid', $request->uuid)->first()->id;

        if($request->accrual_method == 'points') $request->accrual_value = $request->accrual_value * 100;

        if($request->writeoff_method == 'points') $request->writeoff_value = $request->writeoff_value * 100;

        try {
            DB::transaction(function () use ($request, $serviceItem) {
                BusinessmanService::create([
                    'uuid' => Str::uuid(),
                    'businessmen_id' => auth('api')->user()->id,
                    'service_item_id' => $serviceItem,
                    'accrual_method' => $request->accrual_method,
                    'writeoff_method' => $request->writeoff_method,
                    'accrual_value' => $request->accrual_value,
                    'writeoff_value' => $request->writeoff_value,
                ]);
            });
        } catch (\Throwable $th) {
            return response()->json(['error'=>$th], 500);      
        }

        return $this->sendResponse([],'');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($uuid)
    {
        return $this->sendResponse(BusinessmanService::join('service_items', 'businessman_services.service_item_id', '=', 'service_items.id')
        ->where('businessman_services.businessmen_id', auth('api')->user()->id)
        ->where('businessman_services.uuid', $uuid)
        ->select('businessman_services.uuid', 'service_items.name', 'businessman_services.accrual_method', 'businessman_services.accrual_value', 'businessman_services.writeoff_method', 'businessman_services.writeoff_value')
        ->get()
        ->toArray(),'Услуга');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($uuid)
    {
        return $this->sendResponse(BusinessmanService::join('service_items', 'businessman_services.service_item_id', '=', 'service_items.id')
        ->where('businessman_services.businessmen_id', auth('api')->user()->id)
        ->where('businessman_services.uuid', $uuid)
        ->select('businessman_services.uuid', 'service_items.name', 'businessman_services.accrual_method', 'businessman_services.accrual_value', 'businessman_services.writeoff_value', 'businessman_services.writeoff_value')
        ->get()
        ->toArray(),'Редактировать услугу');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $uuid)
    {
        $validator = Validator::make($request->all(), [ 
            'item_uuid' => 'required|uuid|exists:service_items,uuid',
            'accrual_method' => [
                'required',
                Rule::in(['points', 'percent']), // предприниматель, покупатель
            ],
            'writeoff_method' => [
                'required',
                Rule::in(['points', 'percent']), // предприниматель, покупатель
            ],
            'accrual_value' => 'required|integer',
            'writeoff_value' => 'required|integer'
        ]);

        if ($validator->fails()) { 
            return response()->json(['errors'=>$validator->errors()], 400);            
        }

        $item = ServiceItem::where('uuid', $request->item_uuid)->first()->id;
        BusinessmanService::where('uuid', $uuid)->update([
            'businessmen_id' => $item,
            'accrual_method' => $request->accrual_method,
            'writeoff_method' => $request->writeoff_method,
            'accrual_value' => $request->accrual_value,
            'writeoff_value' => $request->writeoff_value,
        ]);

        return $this->sendResponse([],'Updated');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($uuid)
    {
        BusinessmanService::where('uuid', $uuid)->delete();
        return $this->sendResponse([],'Updated');
    }
}
