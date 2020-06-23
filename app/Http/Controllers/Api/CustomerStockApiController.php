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
use App\Models\ClientToStock;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CustomerStockApiController extends ApiBaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $res = Stock::join('service_items', 'stocks.service_id', '=', 'service_items.id')
        ->select('stocks.id','stocks.uuid', 'service_items.name AS service_name', 'stocks.name AS name', 'stocks.description', 'stocks.photo', 'stocks.expires_at')
        ->get()
        ->toArray();
        $stocks = [];
        foreach($res as $value) {
            $stocks[$value['id']] = $value;
            $stocks[$value['id']]['is_favorite'] = 0;
            unset($stocks[$value['id']]['id']);
        }

        $favorites = ClientToStock::whereIn('stock_id', array_keys($stocks))->where('customer_id', auth('api')->user()->id)->get();

        foreach($favorites as $value) {
            $stocks[$value->stock_id]['is_favorite'] = 1;
        }

        return $this->sendResponse(array_values($stocks),'Список акций');
    }

    public function filterStock(Request $request)
    {
        if($request->uuid != null) $service = ServiceItem::where('uuid', $request->uuid)->first()->id;
        else $service = null;
        if($request->city != null && $service != null)
        {
            return $this->sendResponse(Stock::join('service_items', 'stocks.service_id', '=', 'service_items.id')
            ->where('stocks.city', $request->city)
            ->where('stocks.service_id', $service)
            ->select('stocks.uuid', 'service_items.name AS service_name', 'stocks.name AS name', 'stocks.description', 'stocks.photo', 'stocks.expires_at')
            ->get()
            ->toArray(),'Список акций');
        }
        if($request->city == null && $service != null)
        {
            return $this->sendResponse(Stock::join('service_items', 'stocks.service_id', '=', 'service_items.id')
            ->where('stocks.service_id', $service)
            ->select('stocks.uuid', 'service_items.name AS service_name', 'stocks.name AS name', 'stocks.description', 'stocks.photo', 'stocks.expires_at')
            ->get()
            ->toArray(),'Список акций');
        }
        if($request->city != null && $service == null)
        {
            return $this->sendResponse(Stock::join('service_items', 'stocks.service_id', '=', 'service_items.id')
            ->where('stocks.city', $request->city)
            ->select('stocks.uuid', 'service_items.name AS service_name', 'stocks.name AS name', 'stocks.description', 'stocks.photo', 'stocks.expires_at')
            ->get()
            ->toArray(),'Список акций');
        }
        if($request->city == null && $service == null)
        {
            return $this->sendResponse(Stock::join('service_items', 'stocks.service_id', '=', 'service_items.id')
            ->select('stocks.uuid', 'service_items.name AS service_name', 'stocks.name AS name', 'stocks.description', 'stocks.photo', 'stocks.expires_at')
            ->get()
            ->toArray(),'Список акций');
        }
    }

    public function addToFavorite(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'stock_uuid' => 'required|uuid|exists:stocks,uuid'
        ]);

        if ($validator->fails()) { 
            return response()->json(['errors'=>$validator->errors()], 400);            
        }

        $targetStockId = Stock::where('uuid', $request->stock_uuid)->value('id');

        $clientToStock = ClientToStock::firstOrCreate(
            ['customer_id' => auth('api')->user()->id, 'stock_id' => $targetStockId]
        );

        return $this->sendResponse([], 'Добавлено в избранное');
    }

    public function getFavoriteStocks()
    {
        $favorites = ClientToStock::join('stocks', 'client_to_stock.stock_id', '=', 'stocks.id')
            ->join('service_items', 'stocks.service_id', '=', 'service_items.id')
            ->select('stocks.uuid', 'service_items.name AS service_name', 'stocks.name AS name', 'stocks.description', 'stocks.photo', 'stocks.expires_at')
            ->where('client_to_stock.customer_id', auth('api')->user()->id)
            ->get();

        return $this->sendResponse($favorites->toArray(), 'Список избранных акций');
    }
}
