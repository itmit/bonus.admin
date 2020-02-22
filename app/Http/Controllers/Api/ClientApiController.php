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
use App\Models\CustomerService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

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
        if($client->type == 'businessman')
        {
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
        if($client->type == 'customer')
        {
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
        
        return $this->sendResponse($result,'Клиент');
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
