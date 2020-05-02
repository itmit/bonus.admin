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
    public function show($id)
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
                $clientUpdateArray['phone'] = $request->phone;
            }
            if ($request->email) {
                $rules['email'] = 'required|unique:clients';
                $clientUpdateArray['email'] = $request->email;
            }
            if ($request->name) {
                $rules['name'] = 'required';
                $clientUpdateArray['name'] = $request->email;
            }

            if (count($rules) > 0 && count($clientUpdateArray) > 0) {
                $validator = Validator::make($request->all(), $rules);

                if ($validator->fails()) {
                    return response()->json(['errors' => $validator->errors()], 400);
                }

                Client::where('client_id', $client->id)->update([
                    'phone' => $request->phone,
                    'name' => $request->name,
                    'email' => $request->email,
                ]);
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
            foreach($request->toArray() as $key => $value) {
               if (in_array($key, $fields) && $value){
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
            Client::where('client_id', $client->id)->update([
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
}
