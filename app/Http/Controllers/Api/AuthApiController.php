<?php

namespace App\Http\Controllers\Api;

use App\Models\Client;
use App\Models\ClientBusinessman;
use App\Models\ClientCustomer;
use App\Models\BusinessmanManager;
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
            return response()->json(['errors' => $validator->errors()], 401);
        }

        if (Client::where('email', '=', $request->email)->exists()) {
            return response()->json(['error' => 'Клиент уже зарегистрирован'], 401);
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

                if ($request->device_token) {
                    Client::where('id', '=', $this->user->id)->update([
                        'device_token' => $request->device_token
                    ]);
                };
            });
        } catch (\Throwable $th) {
            return response()->json(['error' => $th], 401);
        }

        return $this->sendResponse(['uuid' => $this->user->uuid], 'Зарегистрировано');
    }

    /** 
     * login api 
     * 
     * @return Response 
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'login' => 'required',
            'password' => 'required|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 401);
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

        if (!$client) {
            return response()->json(['error' => 'Такого пользователя не существует'], 401);
        }

        if ($client->type == 'businessman') {
            $isInfoFilled = ClientBusinessman::where('client_id', '=', $client->id)->exists();
        }
        if ($client->type == 'customer') {
            $isInfoFilled = ClientCustomer::where('client_id', '=', $client->id)->exists();
        }
        if ($client->type == 'manager') {
            $isInfoFilled = true;
        }

        if (!$isInfoFilled) {
            return $this->sendResponse(['uuid' => $client->uuid, 'type' => $client->type], 'Данные о пользователе не заполнены', false);
        }
        
        if($request->device_token)
        {
            Client::where('id', '=', $client->id)->update([
                'device_token' => $request->device_token
            ]);
        }
        
        return self::auth($client, $request->password);
    }

    public function logout(Request $request)
    {
        $isUser = $request->client()->token()->revoke();
        if ($isUser) {
            return $this->sendResponse([], "Successfully logged out.");
        } else {
            return $this->sendResponse([], "Something went wrong.");
        }
    }

    public function fillInfo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'uuid' => 'required|uuid',
            'country' => 'required|string',
            'city' => 'required|string',
            'address' => 'min:6',
            'worktime' => 'string',
            'phone' => 'string',
            'contact' => 'string',
            'photo' => 'image|mimes:jpeg,jpg,png,gif|max:10000',
            'password' => 'required|min:6'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 401);
        }

        $params = $request->all();

        $phone = $params['phone'];

        $phoneNumberUtil = \libphonenumber\PhoneNumberUtil::getInstance();
        $phoneNumberObject = $phoneNumberUtil->parse($phone, null);
        if (!$phoneNumberUtil->isPossibleNumber($phoneNumberObject)) {
            return response()->json(['error' => 'Некорректный номер'], 500);
        }
        $phone = $phoneNumberUtil->format($phoneNumberObject, \libphonenumber\PhoneNumberFormat::E164);

        $params['phone'] = $phone;

        $client = Client::where('uuid', $request->uuid)->first();

        if (!$client) return response()->json(['error' => 'Пользователь не найден'], 400);

        if (!Hash::check($request->password, $client->password)) return response()->json(['error' => 'Неверный пароль'], 401);

        if (ClientBusinessman::where('client_id', $client->id)->exists() || ClientCustomer::where('client_id', $client->id)->exists()) return response()->json(['error' => 'Данные о пользователе уже заполнены'], 500);

        if ($request->photo != NULL) {
            $path = $request->photo->store('public/avatars');
            $url = Storage::url($path);
        } else $url = NULL;

        if ($client->type == 'businessman') {
            $validator = Validator::make($params, [
                'uuid' => 'required|uuid',
                'country' => 'required|string',
                'city' => 'required|string',
                'address' => 'required|min:6',
                'worktime' => 'required|string',
                'contact' => 'required|string',
                'phone' => 'required|string|unique:clients',
                'photo' => 'image|mimes:jpeg,jpg,png,gif|max:10000',
                'password' => 'required|min:6'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 500);
            }
        };

        if ($client->type == 'customer') {
            $validator = Validator::make($params, [
                'uuid' => 'required|uuid',
                'country' => 'required|string',
                'city' => 'required|string',
                'sex' => [
                    'required',
                    Rule::in(['male', 'female']),
                ],
                'birthday' => 'required|date',
                'phone' => 'required|string|unique:clients',
                'photo' => 'image|mimes:jpeg,jpg,png,gif|max:10000',
                'password' => 'required|min:6'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 500);
            }
        }

        try {
            DB::transaction(function () use ($params, $client, $url) {
                if ($client->type == 'businessman') {
                    ClientBusinessman::create([
                        'client_id' => $client->id,
                        'country' => $params['country'],
                        'city' => $params['city'],
                        'address' => $params['address'],
                        'work_time' => $params['worktime'],
                        'contact' => $params['contact'],
                        'description' => $params['description'],
                        'photo' => $url,
                    ]);

                    Client::where('uuid', $params['uuid'])->update([
                        'phone' => $params['phone'],
                        'active' => 1,
                    ]);
                };

                if ($client->type == 'customer') {
                    ClientCustomer::create([
                        'client_id' => $client->id,
                        'country' => $params['country'],
                        'city' => $params['city'],
                        'sex' => $params['sex'],
                        'birthday' => $params['birthday'],
                        'car' => $params['car'],
                        'photo' => $url,
                    ]);

                    Client::where('uuid', $params['uuid'])->update([
                        'phone' => $params['phone'],
                        'active' => 1,
                    ]);
                }
            });
        } catch (\Throwable $th) {
            return response()->json(['errors' => $th], 500);
        }

        return self::auth(Client::where('uuid', $request->uuid)->first(), $request->password);
    }

    public function authorizationAnExternalService(Request $request) {
        $validator = Validator::make($request->all(), [
            'access_token' => 'required|string',
            'email' => 'required|email|max:191',
            'service' => [
                'required',
                Rule::in(['vk', 'facebook']), // предприниматель, покупатель
            ],
            'device_token' => 'string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 401);
        }
        if ($request->service == "vk") {
            $url = "https://api.vk.com/method/secure.checkToken?token=$request->access_token&v=5.37&client_secret=tidyZbYM2oE8zoijD4zV&access_token=dbfad886dbfad886dbfad886a2db8845e7ddbfadbfad886851dffc451280fb30fbe4186";
        } else{
            $url = "https://graph.facebook.com/me?access_token=$request->access_token";
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ch);
        curl_close($ch);
        
        $result = json_decode($result);

        $isSuccess = false;
        if ($request->service == "vk")
        {
            $isSuccess = $result->response->success;
        }else{
            $isSuccess = property_exists($result, "id");
        }
        if ($isSuccess)
            {
                if($request->device_token)
                {
                    Client::where('email', $request->email)->update([
                        'device_token' => $request->device_token
                    ]);
                }

                $client = Client::where('email', $request->email)->first();
                Auth::login($client);
                if (Auth::check()) {
                    $tokenResult = $client->createToken(config('app.name'));
                    $token = $tokenResult->token;
                    $token->expires_at = Carbon::now()->addWeeks(1);
                    $token->save();

                    if ($client->type == 'businessman') $clientInfo = ClientBusinessman::where('client_id', $client->id)->first();
                    if ($client->type == 'customer') $clientInfo = ClientCustomer::where('client_id', $client->id)->first();

                    return $this->sendResponse(
                        [
                            'client' => $client,
                            'client_info' => $clientInfo,
                            'access_token' => $tokenResult->accessToken,
                            'token_type' => 'Bearer',
                            'expires_at' => Carbon::parse(
                                $tokenResult->token->expires_at
                            )->toDateTimeString(),
                        ],
                        'Authorization is successful'
                    );
                }
            }
    }

    private function auth($client, $password)
    {
        if (Hash::check($password, $client->password)) {
            Auth::login($client);
            if (Auth::check()) {
                $tokenResult = $client->createToken(config('app.name'));
                $token = $tokenResult->token;
                $token->expires_at = Carbon::now()->addWeeks(1);
                $token->save();

                if ($client->type == 'businessman') $clientInfo = ClientBusinessman::where('client_id', $client->id)->first();
                if ($client->type == 'customer') $clientInfo = ClientCustomer::where('client_id', $client->id)->first();
                if ($client->type == 'manager') $clientInfo = BusinessmanManager::where('client_id', $client->id)->first();

                return $this->sendResponse(
                    [
                        'client' => $client,
                        'client_info' => $clientInfo,
                        'access_token' => $tokenResult->accessToken,
                        'token_type' => 'Bearer',
                        'expires_at' => Carbon::parse(
                            $tokenResult->token->expires_at
                        )->toDateTimeString(),
                    ],
                    'Authorization is successful'
                );
            }
        } else {
            return response()->json(['error' => 'Неверный пароль'], 401);
        }
    }

    public function sendCode(Request $request)
    {
        $validator = Validator::make($request->all(), [ 
            'email' => 'required|email|exists:clients',
        ]);
        
        if ($validator->fails()) { 
            return response()->json(['errors'=>$validator->errors()], 401);            
        }

        $code = random_int(1000, 9999);
        $message = "Ваш код для сброса пароля: " . $code;

        Client::where('email', $request->email)->update([
            'code' => $code
        ]);

        // На случай если какая-то строка письма длиннее 70 символов мы используем wordwrap()
        $message = wordwrap($message, 70, "\r\n");

        // Отправляем
        mail($request->email, 'Приложение БОНУС. Сброс пароля', $message);
    }
    
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [ 
            'email' => 'required|email|exists:clients',
            'code' => 'required',
            'password' => 'required|confirmed'
        ]);
        
        if ($validator->fails()) { 
            return response()->json(['errors'=>$validator->errors()], 401);            
        }

        $client = Client::where('email', $request->email)->first();

        if($request->code == $client->code)
        {
            $client->update([
                'password' => Hash::make($request->password),
                'code' => null
            ]);
        }
        else return response()->json(['error'=>'Wrong code'], 400);     

        return $this->sendResponse([], 'Пароль успешно сменен');
    }
}
