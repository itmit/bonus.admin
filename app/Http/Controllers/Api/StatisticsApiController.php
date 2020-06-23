<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientToBusinessman;
use App\Models\CustomerService;
use App\Models\ServiceType;
use App\Models\BusinessmanService;
use App\Models\ProfileView;
use App\Models\Stock;
use App\Models\StockView;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class StatisticsApiController extends ApiBaseController
{
    public function getAgeStatistics(Request $request)
    {
        $validator = Validator::make($request->all(), [ 
            'date_from' => 'required|date',
            'date_to' => 'required|date',
        ]);

        if ($validator->fails()) { 
            return response()->json(['errors'=>$validator->errors()], 400);            
        }

        $ageArray = array();

        $ranges = [ // the end of each age-range.
            'до 18' => 18,
            '18-21' => 21,
            '21-30' => 30,
            '30-40' => 40,
            '40-50' => 50,
            'от 50' => 200,
        ];

        // $fromDate = date_create('2020-01-01');
        // $tillDate = date("Y-m-d");

        $fromDate = date_create($request->date_from);
        $tillDate = date_create($request->date_to);

        // Получить список подписчиков предпринимателя
        $maleAgeRanges = ClientToBusinessman::select('client_customers.birthday')
            ->join('client_customers', 'client_to_businessman.customer_id', 'client_customers.client_id')
            ->where('businessman_id', auth('api')->user()->id)
            ->where('client_customers.sex', 'male')
            ->where('is_active', 1)
            ->whereBetween('client_to_businessman.updated_at', [$fromDate, $tillDate])
            ->get()
            ->map(function ($user) use ($ranges) {
                $age = Carbon::parse($user->birthday)->age;
                foreach($ranges as $key => $breakpoint)
                {
                    if ($breakpoint >= $age)
                    {
                        $user->range = $key;
                        break;
                    }
                }

                return $user;
            })
            ->mapToGroups(function ($user, $key) {
                return [$user->range => $user];
            })
            ->map(function ($group) {
                return count($group);
            })
            ->sortKeys();

        $femaleAgeRanges = ClientToBusinessman::select('client_customers.birthday')
            ->join('client_customers', 'client_to_businessman.customer_id', 'client_customers.client_id')
            ->where('businessman_id', auth('api')->user()->id)
            ->where('client_customers.sex', 'female')
            ->where('is_active', 1)
            ->whereBetween('client_to_businessman.updated_at', [$fromDate, $tillDate])
            ->get()
            ->map(function ($user) use ($ranges) {
                $age = Carbon::parse($user->birthday)->age;
                foreach($ranges as $key => $breakpoint)
                {
                    if ($breakpoint >= $age)
                    {
                        $user->range = $key;
                        break;
                    }
                }

                return $user;
            })
            ->mapToGroups(function ($user, $key) {
                return [$user->range => $user];
            })
            ->map(function ($group) {
                return count($group);
            })
            ->sortKeys();

        // собрать конечный массив
        foreach ($ranges as $key => $range) {
            $rangeObj = new \stdClass();
            $rangeObj->age = $key;
            $rangeObj->male = $maleAgeRanges->has($key) ? $maleAgeRanges[$key] : 0;
            $rangeObj->female = $femaleAgeRanges->has($key)  ? $femaleAgeRanges[$key] : 0;

            array_push($ageArray, $rangeObj);
        }

        return $this->sendResponse($ageArray, 'Age statistics created');
    }

    public function getGeographyStatistics(Request $request)
    {
        $validator = Validator::make($request->all(), [ 
            'date_from' => 'required|date',
            'date_to' => 'required|date',
            'type' => [
                'required',
                Rule::in(['city', 'country']), // баллы, проценты
            ],
        ]);

        if ($validator->fails()) { 
            return response()->json(['errors'=>$validator->errors()], 400);            
        }

        $percentageArr = array();
        $statisticsArray = array();
        $statisticsType = $request->type; // city or country

        $fromDate = date_create($request->date_from);
        $tillDate = date_create($request->date_to);

        // Получить список подписчиков предпринимателя
        $suscribersByLocation = ClientToBusinessman::select('city', 'country')
            ->join('client_customers', 'client_to_businessman.customer_id', 'client_customers.client_id')
            ->where('businessman_id', auth('api')->user()->id)
            ->where('is_active', 1)
            ->whereBetween('client_to_businessman.updated_at', [$fromDate, $tillDate])
            ->get()
            ->mapToGroups(function ($user, $key) use ($statisticsType) {
                if ($statisticsType == 'city') {
                    return [$user->city => $user];
                }

                return [$user->country => $user];
            })
            ->map(function ($group) {
                return count($group);
            })
            ->sortKeys();

        $totalSubs = $suscribersByLocation->sum();

        // посчитать процент для каждой локации
        foreach ($suscribersByLocation as $key => $suscribersCount) {
            if($suscribersCount != 0){
                $percentageArr[$key] = round(($suscribersCount / $totalSubs) * 100, 0);
            }
        }

        // собрать конечный массив
        foreach ($percentageArr as $key => $percentage) {
            $percentageObj = new \stdClass();
            $percentageObj->name = $key;
            $percentageObj->percent = $percentage;

            array_push($statisticsArray, $percentageObj);
        }

        return $this->sendResponse($statisticsArray, 'Geography statistics created');
    }

    public function getSalesStatistics(Request $request)
    {
        $validator = Validator::make($request->all(), [ 
            'date_from' => 'required|date',
            'date_to' => 'required|date',
            'service_type_ids' => 'required|json'
        ]);

        if ($validator->fails()) { 
            return response()->json(['errors'=>$validator->errors()], 400);            
        }

        $resultArray = array();

        $serviceTypeIds = json_decode($request->service_type_ids);
        $fromDate = date_create($request->date_from);
        $tillDate = date_create($request->date_to);
        
        foreach ($serviceTypeIds as $typeId) {
            $serviceSalesByDate = CustomerService::select('customer_services.id', 'customer_services.created_at')
                ->where('service_id', $typeId)
                ->whereBetween('customer_services.created_at', [$fromDate, $tillDate])
                ->get()
                ->groupBy(function($item){ return $item->created_at->format('d.m'); })
                ->map(function ($group) {
                    return count($group);
                })
                ->sortKeys();
                
            $serviceTypeName = BusinessmanService::where('businessman_services.id', $typeId)
            ->join('service_items' , 'service_items.id', 'businessman_services.service_item_id')->value('name');

            $serviceObj = new \stdClass();
            $serviceObj->name = $serviceTypeName;

            $salesStatByDate = array();

            foreach ($serviceSalesByDate as $key => $value) {
                $saleObj = new \stdClass();
                $saleObj->count = $value;
                $saleObj->date = $key;

                array_push($salesStatByDate, $saleObj);
            }

            $serviceObj->sales = $salesStatByDate;

            array_push($resultArray, $serviceObj);
        }

        return $this->sendResponse($resultArray, 'Sales statistics created');
    }

    public function getProfileViewsStatistics(Request $request)
    {
        $validator = Validator::make($request->all(), [ 
            'profile_uuid' => 'required|uuid|exists:clients,uuid',
            'date_from' => 'required|date',
            'date_to' => 'required|date'
        ]);

        if ($validator->fails()) { 
            return response()->json(['errors'=>$validator->errors()], 400);            
        }

        $resultArray = array();

        $profileId = Client::where('uuid', $request->profile_uuid)->value('id');
        $fromDate = date_create($request->date_from);
        $tillDate = date_create($request->date_to);

        // Create views object
        $viewsTypeObj = $this->getAllProfileViewsByDate($profileId, $fromDate, $tillDate);
        array_push($resultArray, $viewsTypeObj);

        $uniqueViewsTypeObj = $this->getUniqueProfileViewsByDate($profileId, $fromDate, $tillDate);

        array_push($resultArray, $uniqueViewsTypeObj);

        return $this->sendResponse($resultArray, 'Profile views statistics created');
    }

    public function getStockViewsStatistics(Request $request)
    {
        $validator = Validator::make($request->all(), [ 
            'date_from' => 'required|date',
            'date_to' => 'required|date'
        ]);

        if ($validator->fails()) { 
            return response()->json(['errors'=>$validator->errors()], 400);            
        }

        $resultArray = array();

        $fromDate = date_create($request->date_from);
        $tillDate = date_create($request->date_to);

        // Create views object
        $viewsTypeObj = $this->getAllStockViewsByDate($fromDate, $tillDate);
        array_push($resultArray, $viewsTypeObj);

        $uniqueViewsTypeObj = $this->getUniqueStockViewsByDate($fromDate, $tillDate);

        array_push($resultArray, $uniqueViewsTypeObj);

        return $this->sendResponse($resultArray, 'Stock views statistics created');
    }

    private function getAllProfileViewsByDate($profileId, $fromDate, $tillDate)
    {
        $typeName = "Просмотры";

        // Create views object
        $viewsByDate = ProfileView::select('id', 'created_at')
            ->where('profile_id', $profileId)
            ->whereBetween('created_at', [$fromDate, $tillDate])
            ->get()
            ->groupBy(function($item){ return $item->created_at->format('d.m'); })
            ->map(function ($group) {
                return count($group);
            })
            ->sortKeys();

        $viewsTypeObj = new \stdClass();
        $viewsTypeObj->name = $typeName;

        $viewsStatByDate = array();

        foreach ($viewsByDate as $key => $value) {
            $viewsObj = new \stdClass();
            $viewsObj->count = $value;
            $viewsObj->date = $key;

            array_push($viewsStatByDate, $viewsObj);
        }

        $viewsTypeObj->views = $viewsStatByDate;

        return $viewsTypeObj;
    }

    private function getUniqueProfileViewsByDate($profileId, $fromDate, $tillDate)
    {
        $typeName = "Уникальные посетители";

        // Create unique views object
        $viewsByDate = ProfileView::select('id', 'ip', 'created_at')
            ->where('profile_id', $profileId)
            ->whereBetween('created_at', [$fromDate, $tillDate])
            ->get()
            ->groupBy(function($item){ return $item->created_at->format('d.m'); })
            ->map(function ($group) {
                $result = $group->groupBy('ip')->map(function ($subGroup) {return count($subGroup); });
                return count($result);
            })
            ->sortKeys();

        $uniqueViewsTypeObj = new \stdClass();
        $uniqueViewsTypeObj->name = $typeName;

        $viewsStatByDate = array();

        foreach ($viewsByDate as $key => $value) {
            $viewsObj = new \stdClass();
            $viewsObj->count = $value;
            $viewsObj->date = $key;

            array_push($viewsStatByDate, $viewsObj);
        }

        $uniqueViewsTypeObj->views = $viewsStatByDate;

        return $uniqueViewsTypeObj;
    }

    private function getAllStockViewsByDate($fromDate, $tillDate)
    {
        $typeName = "Просмотры";

        // Create views object
        $viewsByDate = StockView::select('stock_views.id', 'stock_views.created_at')
            ->join('stocks', 'stocks.id', 'stock_views.stock_id')
            ->where('stocks.client_id', auth('api')->user()->id)
            ->whereBetween('stock_views.created_at', [$fromDate, $tillDate])
            ->get()
            ->groupBy(function($item){ return $item->created_at->format('d.m'); })
            ->map(function ($group) {
                return count($group);
            })
            ->sortKeys();

        $viewsTypeObj = new \stdClass();
        $viewsTypeObj->name = $typeName;

        $viewsStatByDate = array();

        foreach ($viewsByDate as $key => $value) {
            $viewsObj = new \stdClass();
            $viewsObj->count = $value;
            $viewsObj->date = $key;

            array_push($viewsStatByDate, $viewsObj);
        }

        $viewsTypeObj->views = $viewsStatByDate;

        return $viewsTypeObj;
    }

    private function getUniqueStockViewsByDate($fromDate, $tillDate)
    {
        $typeName = "Уникальные посетители";
        
        // Create unique views object
        $viewsByDate = StockView::select('stock_views.id', 'stock_views.ip', 'stock_views.created_at')
            ->join('stocks', 'stocks.id', 'stock_views.stock_id')
            ->where('stocks.client_id', auth('api')->user()->id)
            ->whereBetween('stock_views.created_at', [$fromDate, $tillDate])
            ->get()
            ->groupBy(function($item){ return $item->created_at->format('d.m'); })
            ->map(function ($group) {
                $result = $group->groupBy('stock_views.ip')->map(function ($subGroup) {return count($subGroup); });
                return count($result);
            })
            ->sortKeys();

        $uniqueViewsTypeObj = new \stdClass();
        $uniqueViewsTypeObj->name = $typeName;

        $viewsStatByDate = array();

        foreach ($viewsByDate as $key => $value) {
            $viewsObj = new \stdClass();
            $viewsObj->count = $value;
            $viewsObj->date = $key;

            array_push($viewsStatByDate, $viewsObj);
        }

        $uniqueViewsTypeObj->views = $viewsStatByDate;

        return $uniqueViewsTypeObj;
    }
}
