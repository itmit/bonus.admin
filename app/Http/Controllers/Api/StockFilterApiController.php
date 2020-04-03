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

class StockFilterApiController extends ApiBaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $cities = Stock::select('city')->distinct()->get();
        $services = Stock::join('service_items', 'stocks.service_id', '=', 'service_items.id')
        ->select('service_items.name AS name', 'service_items.uuid AS uuid')
        ->distinct()->get();
        $result = [
            'cities' => $cities,
            'services' => $services
        ];
        return $this->sendResponse($result, '');



        return $this->sendResponse(Stock::join('service_items', 'stocks.service_id', '=', 'service_items.id')
        ->select('stocks.city', 'service_items.name AS service_name', 'service_items.uuid AS uuid')
        ->distinct()
        ->get()
        ->toArray(),'Список городов и услуг');
    }
}
