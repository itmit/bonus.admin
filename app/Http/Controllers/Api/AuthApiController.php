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
                    'type' => $request->type,
                    'password' => Hash::make($request->password),
                ]);
    
                if($request->device_token)
                {
                    Client::where('id', '=', $this->user->id)->update([
                        'device_token' => $request->device_token
                    ]);
                };
    
                return $this->sendResponse([],'Зарегистрировано');

            });
        } catch (\Throwable $th) {
            return response()->json(['error'=>'Не удалось зарегистрировать клиента'], 401);      
        }
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
            $client = Client::where('phone', request('login'))->first();
        }

        if(!Client::where('phone', '=', $request->phone)->exists())
        {
            return response()->json(['error'=>'Такого пользователя не существует'], 401); 
        }       

        $client = Client::where('phone', '=', $request->phone)->first();

        if(Hash::check($request->password, $client->password))
        {
            Auth::login($client);
            if (Auth::check()) {
                $tokenResult = $client->createToken(config('app.name'));
                $token = $tokenResult->token;
                $token->expires_at = Carbon::now()->addWeeks(1);
                $token->save();

                if($request->device_token)
                {
                    Client::where('id', '=', $client->id)->update([
                        'device_token' => $request->device_token
                    ]);
                }

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
        return response()->json(['error'=>'Авторизация не удалась'], 401); 
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
        
    }
}
