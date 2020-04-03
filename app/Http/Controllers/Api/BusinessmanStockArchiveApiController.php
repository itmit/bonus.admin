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

class BusinessmanStockArchiveApiController extends ApiBaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->sendResponse(StockArchive::join('service_items', 'stock_archives.service_id', '=', 'service_items.id')
        ->where('stock_archives.client_id', auth('api')->user()->id)
        ->select('stock_archives.uuid', 'service_items.name AS service_name', 'stock_archives.name AS name', 'stock_archives.description', 'stock_archives.photo', 'stock_archives.expires_at', 'stock_archives.created')
        ->get()
        ->toArray(),'Список акций');
    }

    public function filterStock(Request $request)
    {
        if($request->uuid) $service = ServiceItem::where('uuid', $request->uuid)->first()->id;
        else $service = null;
        return $this->sendResponse(StockArchive::join('service_items', 'stock_archives.service_id', '=', 'service_items.id')
        ->where('stock_archives.client_id', auth('api')->user()->id)
        ->where('stock_archives.city', $request->city)
        ->where('stock_archives.service_id', $service)
        ->select('stock_archives.uuid', 'service_items.name AS service_name', 'stock_archives.name AS name', 'stock_archives.description', 'stock_archives.photo', 'stock_archives.expires_at', 'stock_archives.created')
        ->get()
        ->toArray(),'Список акций');
    }
}
