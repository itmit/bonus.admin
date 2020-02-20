<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ServiceType;
use App\Models\ServiceItem;
use App\Models\ClientCustomer;
use App\Models\ClientBusinessman;
use App\Models\ClientBalance;
use Illuminate\Support\Str;

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

    public function getCustomerByUUID()
    {
        $validator = Validator::make($request->all(), [ 
            'uuid' => 'required|uuid|exists:client_customers',
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
                'businessmen_id' => $businessmenId
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
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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
