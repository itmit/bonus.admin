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
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class ServiceApiController extends ApiBaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $result = [];
        $types = ServiceType::orderBy('name')->get(['id', 'uuid', 'name']);
        foreach ($types as $type) {
            $items = ServiceItem::select('uuid', 'name')->where('service_type_id', $type->id)->get();
            $itemsResult = [];
            foreach ($items as $item) {
                $itemsResult[] = $item;
            }
            $result[] = [
                'type' => $type,
                'items' => $itemsResult
            ];
        }
        return $this->sendResponse($result,'');
    }

    public function getCustomerByUUID(Request $request)
    {
        $validator = Validator::make($request->all(), [ 
            'uuid' => 'required|uuid|exists:clients',
        ]);

        if ($validator->fails()) { 
            return response()->json(['errors'=>$validator->errors()], 500);            
        }

        $businessmenId = auth('api')->user()->id;

        $client = Client::where('uuid', $request->uuid)->first();
        $clientCustomer = ClientCustomer::where('id', $client->id)->first();

        $balance = ClientBalance::where('customer_id', $client->id)->where('businessmen_id', $businessmenId)->first();
        if($balance == NULL)
        {
            $balance = ClientBalance::create([
                'uuid' => Str::uuid(),
                'customer_id' => $client->id,
                'businessmen_id' => $businessmenId,
                'amount' => 0
            ]);
        }

        $result = [
            'client' => [
                'name' => $client->name,
                'photo' => $client->photo,
            ],
            'balance' => $balance->amount
        ];

        return $this->sendResponse($result,'');
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
            'value' => 'required|integer'
        ]);

        if ($validator->fails()) { 
            return response()->json(['errors'=>$validator->errors()], 500);            
        }

        $serviceItem = ServiceItem::where('uuid', $request->uuid)->first()->id;

        if($request->accrual_method == 'points') $request->value = $request->value * 100;

        if($request->writeoff_method == 'points') $request->value = $request->value * 100;

        try {
            DB::transaction(function () use ($request, $serviceItem) {
                BusinessmanService::create([
                    'uuid' => Str::uuid(),
                    'businessmen_id' => auth('api')->user()->id,
                    'service_item_id' => $serviceItem,
                    'accrual_method' => $request->accrual_method,
                    'writeoff_method' => $request->writeoff_method,
                    'value' => $request->value,
                ]);
            });
        } catch (\Throwable $th) {
            return response()->json(['error'=>$th], 401);      
        }

        return $this->sendResponse([],'');

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
