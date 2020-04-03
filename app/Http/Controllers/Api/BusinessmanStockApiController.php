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
use App\Models\Stock;
use App\Models\StockArchive;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BusinessmanStockApiController extends ApiBaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->sendResponse(Stock::join('service_items', 'stocks.service_id', '=', 'service_items.id')
        ->where('stocks.client_id', auth('api')->user()->id)
        ->select('stocks.uuid', 'service_items.name AS service_name', 'stocks.name AS name', 'stocks.description', 'stocks.photo', 'stocks.expires_at')
        ->get()
        ->toArray(),'Список акций');
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
            'service_uuid' => 'required|uuid|exists:service_items,uuid',
            'country' => 'required|string',
            'city' => 'required|string',
            'name' => 'required|string',
            'description' => 'required',
            // 'photo' => 'required',
            'expires_at' => 'required|date',
            'sub_only' => [
                'required',
                Rule::in(['0', '1']), // для всех, для подписчиков
            ],
        ]);

        if ($validator->fails()) { 
            return response()->json(['errors'=>$validator->errors()], 400);            
        }

        $serviceItem = ServiceItem::where('uuid', $request->service_uuid)->first()->id;

        if($request->photo != NULL)
        {
            $path = $request->photo->store('public/stock');
            $url = Storage::url($path);
        }
        else $url = NULL;

        try {
            DB::transaction(function () use ($request, $serviceItem, $url) {
                Stock::create([
                    'uuid' => Str::uuid(),
                    'client_id' => auth('api')->user()->id,
                    'service_id' => $serviceItem,
                    'country' => $request->country,
                    'city' => $request->city,
                    'name' => $request->name,
                    'description' => $request->description,
                    'photo' => $url,
                    'expires_at' => $request->expires_at,
                    'sub_only' => $request->sub_only,
                ]);
            });
        } catch (\Throwable $th) {
            return response()->json(['error'=>$th], 500);      
        }

        return $this->sendResponse([], 'Stock created');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($uuid)
    {

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($uuid)
    {
        return $this->sendResponse(Stock::join('service_items', 'stocks.service_id', '=', 'service_items.id')
        ->where('stocks.client_id', auth('api')->user()->id)
        ->where('stocks.uuid', $uuid)
        ->select('stocks.uuid', 'service_items.uuid AS service_uuid', 'stocks.name AS name', 'stocks.description', 'stocks.photo', 'stocks.expires_at',
        'stocks.sub_only', 'stocks.country', 'stocks.city')
        ->get()
        ->toArray(),'Редактировать акцию');
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
            'service_uuid' => 'required|uuid|exists:service_items,uuid',
            'country' => 'required|string',
            'city' => 'required|string',
            'name' => 'required|string',
            'description' => 'required',
            // 'photo' => 'required',
            'expires_at' => 'required|date',
            'sub_only' => [
                'required',
                Rule::in(['0', '1']), // для всех, для подписчиков
            ],
        ]);

        if ($validator->fails()) { 
            return response()->json(['errors'=>$validator->errors()], 400);            
        }

        $item = ServiceItem::where('uuid', $request->item_uuid)->first()->id;

        if($request->photo != NULL)
        {
            $path = $request->photo->store('public/stock');
            $url = Storage::url($path);
        }
        else $url = NULL;
        
        Stock::where('uuid', $uuid)->update([
            'uuid' => Str::uuid(),
            'client_id' => auth('api')->user()->id,
            'service_id' => $item,
            'country' => $request->country,
            'city' => $request->city,
            'name' => $request->name,
            'description' => $request->description,
            'photo' => $url,
            'expires_at' => $request->expires_at,
            'sub_only' => $request->sub_only,
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
        
    }

    public function test(Request $request)
    {
        $yesterday = date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d")-1, date("Y"));
        $stocks = Stock::where('expires_at', '<', $yesterday)->get()->toArray();
        return $this->sendResponse([$yesterday], 'Stocks has been archived');
    }
}
