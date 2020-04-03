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

class CustomerStockApiController extends ApiBaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->sendResponse(Stock::join('service_items', 'stocks.service_id', '=', 'service_items.id')
        ->select('stocks.uuid', 'service_items.name AS service_name', 'stocks.name AS name', 'stocks.description', 'stocks.photo', 'stocks.expires_at')
        ->get()
        ->toArray(),'Список акций');
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
            ->select('stocks.uuid', 'service_items.name AS service_name', 'stocks.name AS name', 'stocks.description', 'stocks.photo', 'stocks.expires_at', 'stocks.created')
            ->get()
            ->toArray(),'Список акций');
        }
        if($request->city == null && $service != null)
        {
            return $this->sendResponse(Stock::join('service_items', 'stocks.service_id', '=', 'service_items.id')
            ->where('stocks.service_id', $service)
            ->select('stocks.uuid', 'service_items.name AS service_name', 'stocks.name AS name', 'stocks.description', 'stocks.photo', 'stocks.expires_at', 'stocks.created')
            ->get()
            ->toArray(),'Список акций');
        }
        if($request->city != null && $service == null)
        {
            return $this->sendResponse(Stock::join('service_items', 'stocks.service_id', '=', 'service_items.id')
            ->where('stocks.city', $request->city)
            ->select('stocks.uuid', 'service_items.name AS service_name', 'stocks.name AS name', 'stocks.description', 'stocks.photo', 'stocks.expires_at', 'stocks.created')
            ->get()
            ->toArray(),'Список акций');
        }
        if($request->city == null && $service == null)
        {
            return $this->sendResponse(Stock::join('service_items', 'stocks.service_id', '=', 'service_items.id')
            ->select('stocks.uuid', 'service_items.name AS service_name', 'stocks.name AS name', 'stocks.description', 'stocks.photo', 'stocks.expires_at', 'stocks.created')
            ->get()
            ->toArray(),'Список акций');
        }
    }
}
