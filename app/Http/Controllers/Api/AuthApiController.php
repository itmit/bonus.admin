<?php

namespace App\Http\Controllers\Api;

use App\Models\Client;
use App\Models\ClientBusinessman;
use App\Models\ClientCustomer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use libphonenumber;

class AuthApiController extends ApiBaseController
{
    public $successStatus = 200;
    public $user;

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [ 
            'email' => 'required|unique:clients|email|max:191',
            'name' => 'required|max:191|min:2|string',
            'login' => 'required|min:2|max:191|string',
            'password' => 'required|confirmed|min:6',
            'type' => [
                'required',
                Rule::in(['businessman', 'customer']), // предприниматель, покупатель
            ],
            'device_token' => 'string'
        ]);
        
        if ($validator->fails()) { 
            return response()->json(['errors'=>$validator->errors()], 401);            
        }

        if(Client::where('email', '=', $request->email)->exists())
        {
            return response()->json(['error'=>'Клиент уже зарегистрирован'], 401);     
        }

        try {
            DB::transaction(function () use ($request) {
                $this->user = Client::create([
                    'uuid' => Str::uuid(),
                    'name' => $request->name,
                    'email' => $request->email,
                    'login' => $request->login,
                    'type' => $request->type,
                    'password' => Hash::make($request->password),
                ]);
    
                if($request->device_token)
                {
                    Client::where('id', '=', $this->user->id)->update([
                        'device_token' => $request->device_token
                    ]);
                };

            });
        } catch (\Throwable $th) {
            return response()->json(['error'=>$th], 401);      
        }

        return $this->sendResponse(['uuid' => $this->user->uuid],'Зарегистрировано');
    }

    /** 
     * login api 
     * 
     * @return Response 
     */ 
    public function login(Request $request) { 

        $validator = Validator::make($request->all(), [ 
            'login' => 'required',
            'password' => 'required|min:6',
        ]);
        
        if ($validator->fails()) { 
            return response()->json(['errors'=>$validator->errors()], 401);            
        }

        if (filter_var(request('login'), FILTER_VALIDATE_EMAIL)) // ЛОГИН ПОЧТА
        {
            $client = Client::where('email', request('login'))->first();
        }
        if (!filter_var(request('login'), FILTER_VALIDATE_EMAIL)) // если ЛОГИН НЕ ПОЧТА
        {
            $phone = request('login');

            $phoneNumberUtil = \libphonenumber\PhoneNumberUtil::getInstance();
            $phoneNumberObject = $phoneNumberUtil->parse($phone, null);
            $phone = $phoneNumberUtil->format($phoneNumberObject, \libphonenumber\PhoneNumberFormat::E164);

            $request->login = $phone;

            $client = Client::where('phone', $request->login)->first();
        }

        if(!$client)
        {
            return response()->json(['error'=>'Такого пользователя не существует'], 401); 
        }

        if($client->type == 'businessman')
        {
            $isInfoFilled = ClientBusinessman::where('client_id', '=', $client->id)->exists();
        }
        if($client->type == 'customer')
        {
            $isInfoFilled = ClientCustomer::where('client_id', '=', $client->id)->exists();
        }

        if(!$isInfoFilled)
        {
            return $this->sendResponse(['uuid' => $client->uuid, 'type' => $client->type], 'Данные о пользователе не заполнены', false);
        }

        return self::auth($client, $request->password);
    }

    public function logout(Request $request)
    {
        $isUser = $request->client()->token()->revoke();
        if($isUser){
            $success['message'] = "Successfully logged out.";
            return $this->sendResponse($success);
        }
        else{
            $error = "Something went wrong.";
            return $this->sendResponse($error);
        }
    }
    
    public function fillInfo(Request $request)
    {
        $validator = Validator::make($request->all(), [ 
            'uuid' => 'required|uuid',
            'country' => 'required|string',
            'city' => 'required|string',
            'sex' => [
                'string',
                Rule::in(['male', 'female']),
            ],
            'birthday' => 'date',
            'car' => 'string',
            'address' => 'min:6',
            'worktime' => 'string',
            'contact' => 'string',
            'phone' => 'required|string|unique:clients',
            'description' => 'string',
            'photo' => 'image|mimes:jpeg,jpg,png,gif|max:10000',
            'password' => 'required|min:6'
        ]);

        if ($validator->fails()) { 
            return response()->json(['errors'=>$validator->errors()], 401);            
        }

        $phone = request('phone');

        $phoneNumberUtil = \libphonenumber\PhoneNumberUtil::getInstance();
        $phoneNumberObject = $phoneNumberUtil->parse($phone, null);
        $phone = $phoneNumberUtil->format($phoneNumberObject, \libphonenumber\PhoneNumberFormat::E164);

        $request->phone = $phone;

        $client = Client::where('uuid', $request->uuid)->first();

        if(!$client) return response()->json(['error'=>'Пользователь не найден'], 400); 

        if(ClientBusinessman::where('client_id', $client->id)->exists() || ClientCustomer::where('client_id', $client->id)->exists()) return response()->json(['error'=>'Данные о пользователе уже заполнены'], 500); 

        if($request->photo != NULL)
        {
            $path = $request->photo->store('public/avatars');
            $url = Storage::url($path);
        }
        else $url = NULL;

        try {
            DB::transaction(function () use ($request, $client, $url) {
                return $this->sendResponse($client->toArray(), 'Authorization is successful');
                if($client->type == 'businessman')
                {
                    $validator = Validator::make($request->all(), [ 
                        'uuid' => 'required|uuid',
                        'country' => 'required|string',
                        'city' => 'required|string',
                        'address' => 'required|min:6',
                        'worktime' => 'required|string',
                        'contact' => 'required|string',
                        'phone' => 'required|string',
                        'description' => 'string',
                        'photo' => 'image|mimes:jpeg,jpg,png,gif|max:10000',
                        'password' => 'required|min:6'
                    ]);
            
                    if ($validator->fails()) { 
                        return response()->json(['errors'=>$validator->errors()], 401);            
                    }
    
                    ClientBusinessman::create([
                        'client_id' => $client->id,
                        'country' => $request->country,
                        'city' => $request->city,
                        'address' => $request->address,
                        'work_time' => $request->worktime,
                        'contact' => $request->contact,
                        'description' => $request->description,
                        'photo' => $url,
                    ]);
    
                    Client::where('uuid', $request->uuid)->update([
                        'phone' => $request->phone,
                        'active' => 1,
                    ]);
                };
    
                if($client->type == 'customer')
                {
                    $validator = Validator::make($request->all(), [ 
                        'uuid' => 'required|uuid',
                        'country' => 'required|string',
                        'city' => 'required|string',
                        'sex' => [
                            'required',
                            Rule::in(['male', 'female']),
                        ],
                        'birthday' => 'required|date',
                        'car' => 'string',
                        'phone' => 'string',
                        'photo' => 'image|mimes:jpeg,jpg,png,gif|max:10000',
                        'password' => 'required|min:6'
                    ]);
            
                    if ($validator->fails()) { 
                        return response()->json(['errors'=>$validator->errors()], 401);            
                    }
    
                    ClientCustomer::create([
                        'client_id' => $client->id,
                        'country' => $request->country,
                        'city' => $request->city,
                        'sex' => $request->sex,
                        'birthday' => $request->birthday,
                        'car' => $request->car,
                        'photo' => $url,
                    ]);
    
                    Client::where('uuid', $request->uuid)->update([
                        'phone' => $request->phone,
                        'active' => 1,
                    ]);
                }
            });
        } catch (\Throwable $th) {
            return response()->json(['errors'=>$th], 401);   
        }
        
        return self::auth($client, $request->password);
    }

    private function auth($client, $password)
    {
        if(Hash::check($password, $client->password))
        {
            Auth::login($client);
            if (Auth::check()) {
                $tokenResult = $client->createToken(config('app.name'));
                $token = $tokenResult->token;
                $token->expires_at = Carbon::now()->addWeeks(1);
                $token->save();

                return $this->sendResponse([
                    'access_token' => $tokenResult->accessToken,
                    'client' => $client,
                    'token_type' => 'Bearer',
                    'expires_at' => Carbon::parse(
                        $tokenResult->token->expires_at
                    )->toDateTimeString(),
                ],
                    'Authorization is successful');
            }
        }
        else
        {
            return response()->json(['error'=>'Неверный пароль'], 401); 
        }
    }
}
