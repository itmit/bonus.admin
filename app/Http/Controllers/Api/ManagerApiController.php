<?php

namespace App\Http\Controllers\Api;

use App\Models\Client;
use App\Models\BusinessmanManager;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class ManagerApiController extends ApiBaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $managers = BusinessmanManager::select('email', 'name', 'phone', 'clients.id')
        ->join('clients', 'businessman_manager.client_id', 'clients.id')
        ->where('businessman_manager.businessman_id', auth('api')->user()->id)
        ->where('clients.deleted_at', null)->get();

        return $this->sendResponse($managers->toArray(), 'Клиент');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $client = Client::findOrFail($id);

        return $this->sendResponse([
            'client' => $client
        ], 'Менеджер');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:191|min:2|string',
            'phone' => 'required|max:191|min:2|string',
            'email' => 'required|unique:clients|email|max:191',
            'password' => 'required|confirmed|min:6'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 401);
        }

        if (Client::where('email', '=', $request->email)->exists()) {
            return response()->json(['error' => 'Клиент уже зарегистрирован'], 401);
        }
        
        try {
            DB::transaction(function () use ($request) {

                $phoneNumberUtil = \libphonenumber\PhoneNumberUtil::getInstance();
                $phoneNumberObject = $phoneNumberUtil->parse($request->phone, null);
                if (!$phoneNumberUtil->isPossibleNumber($phoneNumberObject)) {
                    return response()->json(['error' => 'Некорректный номер'], 500);
                }
                $phone = $phoneNumberUtil->format($phoneNumberObject, \libphonenumber\PhoneNumberFormat::E164);

                $this->user = Client::create([
                    'uuid' => Str::uuid(),
                    'name' => $request->name,
                    'email' => $request->email,
                    'login' => '',
                    'type' => 'manager',
                    'phone' => $phone,
                    'password' => Hash::make($request->password),
                ]);
                BusinessmanManager::create([
                    'client_id' => $this->user->id,
                    'businessman_id' => auth('api')->user()->id
                ]);
            });
        } catch (\Throwable $th) {
            return response()->json(['error' => $th], 401);
        }

        return $this->sendResponse(['uuid' => $this->user->uuid], 'Зарегистрировано');
    }

    /**
     * Update the given user.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => '|max:191|min:2|string',
            'phone' => '|max:191|min:2|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 401);
        }
        
        $client = Client::find($id);
        $client->name = $request->name;
        $client->phone = $request->phone;
        $client->save();

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
        // delete
        $nerd = Client::find($id);
        var_dump($nerd);
        die();
        $nerd->delete();

        return $this->sendResponse([], 'Удалено');
    }
}
