<?php

namespace App\Http\Controllers\Api;

use App\Models\Client;
use App\Models\ServiceItem;
use App\Models\ServiceType;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\ClientBalance;
use App\Models\ClientCustomer;
use App\Models\CustomerService;
use Illuminate\Validation\Rule;
use App\Models\ClientBusinessman;
use App\Models\ClientToBusinessman;
use App\Models\BusinessmanService;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ClientApiController extends ApiBaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $client = Client::where('id', auth('api')->user()->id)->first();
        $result = [];
        if ($client->type == 'businessman') {
            $info = ClientBusinessman::where('client_id', $client->id)->first();
            $result = [
                'uuid' => $client->uuid,
                'email' => $client->email,
                'phone' => $client->phone,
                'login' => $client->login,
                'name' => $client->name,
                'type' => $client->type,
                'country' => $info->country,
                'city' => $info->city,
                'address' => $info->address,
                'work_time' => $info->work_time,
                'contact' => $info->contact,
                'description' => $info->description,
                'photo' => $info->photo,
            ];
        }
        if ($client->type == 'customer') {
            $info = ClientCustomer::where('client_id', $client->id)->first();
            $result = [
                'uuid' => $client->uuid,
                'email' => $client->email,
                'phone' => $client->phone,
                'login' => $client->login,
                'name' => $client->name,
                'type' => $client->type,
                'country' => $info->country,
                'city' => $info->city,
                'sex' => $info->sex,
                'birthday' => $info->birthday,
                'car' => $info->car,
                'photo' => $info->photo,
            ];
        }

        return $this->sendResponse($result, 'Клиент');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($uuid)
    {
        $client = Client::where('uuid', $uuid)->first();
        if ($client->type == 'businessman') {
            \App\Models\ProfileView::createViewLog($client->id);
            $info = ClientBusinessman::where('client_id', $client->id)->first();
        }
        if ($client->type == 'customer') {
            $info = ClientCustomer::where('client_id', $client->id)->first();
        }

        return $this->sendResponse([
            'client' => $client,
            'client_info' => $info
        ], 'Клиент');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $client = Client::where('id', auth('api')->user()->id)->first();
        $result = [];
        if ($client->type == 'businessman') {
            $info = ClientBusinessman::where('client_id', $client->id)->first();
            $result = [
                'uuid' => $client->uuid,
                'email' => $client->email,
                'phone' => $client->phone,
                'login' => $client->login,
                'name' => $client->name,
                'type' => $client->type,
                'country' => $info->country,
                'city' => $info->city,
                'address' => $info->address,
                'work_time' => $info->work_time,
                'contact' => $info->contact,
                'description' => $info->description,
                'photo' => $info->photo,
            ];
        }
        if ($client->type == 'customer') {
            $info = ClientCustomer::where('client_id', $client->id)->first();
            $result = [
                'uuid' => $client->uuid,
                'email' => $client->email,
                'phone' => $client->phone,
                'login' => $client->login,
                'name' => $client->name,
                'type' => $client->type,
                'country' => $info->country,
                'city' => $info->city,
                'sex' => $info->sex,
                'birthday' => $info->birthday,
                'car' => $info->car,
                'photo' => $info->photo,
            ];
        }

        return $this->sendResponse($result, 'Клиент');
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
        $client = Client::where('id', auth('api')->user()->id)->first();
        if ($request->photo != NULL) {
            $path = $request->photo->store('public/avatars');
            $url = Storage::url($path);
        } else {
            $url = NULL;
        }

        if ($client->type == 'businessman') {
            $rules = [];
            $clientUpdateArray = [];
            if ($request->phone) {
                $rules['phone'] = 'required|unique:clients';

                $phoneNumberUtil = \libphonenumber\PhoneNumberUtil::getInstance();
                $phoneNumberObject = $phoneNumberUtil->parse($request->phone, null);
                if (!$phoneNumberUtil->isPossibleNumber($phoneNumberObject)) {
                    return response()->json(['error' => 'Некорректный номер'], 500);
                }
                $clientUpdateArray['phone'] = $phoneNumberUtil->format($phoneNumberObject, \libphonenumber\PhoneNumberFormat::E164);
            }
            if ($request->email) {
                $rules['email'] = 'required|unique:clients';
                $clientUpdateArray['email'] = $request->email;
            }
            if ($request->name) {
                $rules['name'] = 'required';
                $clientUpdateArray['name'] = $request->name;
            }

            if (count($rules) > 0 && count($clientUpdateArray) > 0) {
                $validator = Validator::make($clientUpdateArray, $rules);

                if ($validator->fails()) {
                    return response()->json(['errors' => $validator->errors()], 400);
                }

                Client::where('id', $client->id)->update($clientUpdateArray);
            }

            $fields =  [
                'country',
                'city',
                'address',
                'work_time',
                'contact',
                'description',
                'photo',
                'vk',
                'facebook',
                'instagram',
                'odnoklassniki'
            ];
            $updateArray = [];
            foreach ($request->toArray() as $key => $value) {
                if (in_array($key, $fields) && $value) {
                    $updateArray[$key] = $value;
                }
            }

            ClientBusinessman::where('client_id', $client->id)->update($updateArray);
        }
        if ($client->type == 'customer') {
            if ($request->phone) {
                $validator = Validator::make($request->all(), [
                    'phone' => 'required|unique:clients',
                ]);
            }

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 400);
            }
            Client::where('id', $client->id)->update([
                'phone' => $request->phone,
            ]);
            ClientCustomer::where('client_id', $client->id)->update([
                'country' => $request->country,
                'city' => $request->city,
                'sex' => $request->sex,
                'birthday' => $request->birthday,
                'car' => $request->car,
                'photo' => $url,
            ]);
        }

        return $this->sendResponse([], 'Обновлено');
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

    public function subscribeToBusinessman(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'businessmen_uuid' => 'required|uuid|exists:clients,uuid'
        ]);

        if ($validator->fails()) { 
            return response()->json(['errors'=>$validator->errors()], 400);            
        }

        $targetClientId = Client::where('uuid', $request->businessmen_uuid)->value('id');

        $clientToBusinessman = ClientToBusinessman::updateOrCreate(
            ['customer_id' => auth('api')->user()->id, 'businessman_id' => $targetClientId],
            ['is_active' => 1]
        );

        return $this->sendResponse([], 'Подписка оформлена');
    }

    public function unsubscribeToBusinessman(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'businessmen_uuid' => 'required|uuid|exists:clients,uuid'
        ]);

        if ($validator->fails()) { 
            return response()->json(['errors'=>$validator->errors()], 400);            
        }

        $targetClientId = Client::where('uuid', $request->businessmen_uuid)->value('id');

        ClientToBusinessman::where('customer_id', auth('api')->user()->id)
        ->where('businessman_id', $targetClientId)
        ->delete();

        return $this->sendResponse([], 'Отписка');
    }

    public function getSubscriptuions()
    {
        $currentClient = Client::select('id', 'type')->where('id', auth('api')->user()->id)->first();

        $isBusinessman = ($currentClient->type == "businessman");

        if ($isBusinessman) {
            $subscriptions = ClientToBusinessman::select('clients.id', 'photo', 'name', 'login', 'clients.uuid')
            ->join('client_customers', 'client_to_businessman.customer_id', 'client_customers.client_id')
            ->join('clients', 'client_to_businessman.customer_id', 'clients.id')
            ->where('businessman_id', $currentClient->id)
            ->where('is_active', 1)
            ->get()->toArray();

            foreach($subscriptions as &$item){
                $item['amount'] = 0;
                $users[$item['id']] = $item;
                unset($users[$item['id']]['id']);
            }
    
            $items = ClientBalance::whereIn('customer_id', array_keys($users))->where('businessmen_id', $currentClient->id)->get();
            
            foreach ($items as $item){
                $users[$item->customer_id]['amount'] = $item->amount;
            }

            return $this->sendResponse(array_values($users), 'Список подписок');
        }
        
        $subscriptions = ClientToBusinessman::select('clients.id', 'photo', 'name', 'login', 'clients.uuid')
            ->join('client_businessmen', 'client_to_businessman.businessman_id', 'client_businessmen.client_id')
            ->join('clients', 'client_to_businessman.businessman_id', 'clients.id')
            ->where('customer_id', $currentClient->id)->where('is_active', 1)->get()->toArray();
            $users = [];
        
        foreach($subscriptions as &$item){
            $item['services']=[];
            $users[$item['id']] = $item;
            unset($users[$item['id']]['id']);
        }

        $items = ServiceItem::select('service_items.name', 'client_id')
        ->join('service_types', 'service_items.service_type_id', '=', 'service_types.id')
        ->whereIn('client_id', array_keys($users))
        ->get();
        
        foreach ($items as $item){
            $users[$item->client_id]['services'][] = $item->name;
        }
        
        return $this->sendResponse(array_values($users), 'Список подписок');
    }
}
