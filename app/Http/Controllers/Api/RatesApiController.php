<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\PayRate;
use App\Models\Rate;
use App\Models\Stock;
use App\Models\ClientToRate;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class RatesApiController extends ApiBaseController
{
    public function index()
    {
        return $this->sendResponse(Rate::all()->toArray(), 'Тарифы');
    }

    public function getMyRate()
    {
        $userId = auth('api')->user()->id;
        $stocks =  Stock::where('client_id', $userId)->get()->count();
        $rate = ClientToRate::where('client_to_rate.client_id', $userId)
                ->select('rates.*', 'client_to_rate.expires_at')
                ->join('rates', 'rates.id', 'client_to_rate.rate_id')->get()->first();
        $rate->stock_count = $stocks;
        return $this->sendResponse($rate->toArray(), 'Мой тариф');
    }

    public function payRate()
    {
        $userId = auth('api')->user()->id;

        $rate = ClientToRate::where('client_to_rate.client_id', $userId)
                ->select('rates.*', 'client_to_rate.expires_at')
                ->join('rates', 'rates.id', 'client_to_rate.rate_id')->get()->first();

        $payRate = PayRate::create([
            'client_id' => $userId,
            'rate_id' => $rate->id,
            'count_rates' => 1
        ]);

        $vars['userName'] = 'prilozhenie_4-api';
        $vars['password'] = 'prilozhenie_4';
        
        /* ID заказа в магазине */
        $vars['orderNumber'] = $payRate->id;

        /* Сумма заказа в копейках */
        $vars['amount'] = $rate->price * 1 * 100;
        
        $domain = \Illuminate\Support\Facades\Request::root();

        /* URL куда клиент вернется в случае успешной оплаты */
        $vars['returnUrl'] = $domain .'/api/rates/success';
            
        /* URL куда клиент вернется в случае ошибки */
        $vars['failUrl'] = $domain .'/api/rates/error';
        
        /* Описание заказа, не более 24 символов, запрещены % + \r \n */
        $vars['description'] = 'Оплата тарифа ид оплаты: ' . $payRate->id;

        $ch = curl_init('https://3dsec.sberbank.ru/payment/rest/register.do?' . http_build_query($vars));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $res = curl_exec($ch);
        curl_close($ch);

        $res = json_decode($res, JSON_OBJECT_AS_ARRAY);
        if (empty($res['orderId'])){
            /* Возникла ошибка: */
            return response()->json(['errors'=>[$res['errorMessage']]], 400);      					
        } else {
            /* Успех: */
            $payRate->update(['payment_id' => $res['orderId']]);
            
            return $this->sendResponse(['url' => $res['formUrl']], 'Ссылка на оплату.');
        }
    }

    public function paymentSuccess(Request $request)
    {
        /*
        $vars['userName'] = 'prilozhenie_4-api';
        $vars['password'] = 'prilozhenie_4';
        $vars['orderId'] =  $request->orderId;
        $ch = curl_init('https://3dsec.sberbank.ru/payment/rest/getOrderStatus?' . http_build_query($vars));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $res = curl_exec($ch);
        curl_close($ch);

        $res = json_decode($res, JSON_OBJECT_AS_ARRAY);
        
        if ($res['OrderStatus'] != 2){
            // Возникла ошибка: 
            echo $res['errorMessage'];
            return;					
        }
        */
        $pay = PayRate::where('payment_id', $request->orderId)
            ->where('is_successful', false)->get()->first();

        $pay->update(['is_successful' => true]);
        $rate = Rate::where('rates.id', $pay->rate_id)
            ->select('rates.*', 'client_to_rate.expires_at')
            ->join('client_to_rate', 'rates.id', 'client_to_rate.rate_id')->get()->first();
            
        $date = new Carbon($rate->expires_at);
        $date->addDays($rate->duration * $pay->count_rates);
        ClientToRate::where('rate_id', $pay->rate_id)->update(['expires_at' => $date->format('Y-m-d')]);

        echo "оплата успешна";
    }


    public function paymentError()
    {
        echo "платеж не прошел";
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [ 
            'id' => 'required|integer|exists:rates'
        ]);

        if ($validator->fails()) { 
            return response()->json(['errors'=>$validator->errors()], 400);            
        }
        $userId = auth('api')->user()->id;
        $clientRate = ClientToRate::where('client_to_rate.client_id', $userId);
        
        if ($clientRate != null) {
            $clientRate->delete();
        }

        $rate = Rate::where('id', $request->id);

        $expiretAt = Carbon::now();

        return $this->sendResponse(ClientToRate::create([
            'client_id' => $userId,
            'rate_id' => $request->id,
            'expires_at' => $expiretAt->format('Y-m-d')
        ])->toArray(), 'Тариф подключен');
    }
}