<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Client;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CustomerWebController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $admin = Auth::user()->adm;

        if ( empty($request->all()) ) {
            if ($admin != 1) {
                $managerCity = Auth::user()->city;

                $clients = Client::join('client_customers', 'clients.id', '=', 'client_customers.client_id')
                    ->where('type', 'customer')
                    ->where('client_customers.city', $managerCity)
                    ->get();
            }
            else {
                $clients = Client::where('type', 'customer')->get();
            }

            return view('clients.clientList', [
                'title' => 'Покупатели',
                'clients' => $clients
            ]);
        }

        $clients = Client::query();

        if ($admin != 1) {
            $managerCity = Auth::user()->city;
            $clients->join('client_customers', 'clients.id', '=', 'client_customers.client_id');
            $clients->where('client_customers.city', $managerCity);
        }

        if($request->input('filter_name')){
            $clients = $clients->where('name', 'like', '%' . $request->input('filter_name') . '%');
        }
        if($request->input('filter_email')){
            $clients = $clients->where('email', 'like', '%' . $request->input('filter_email') . '%');
        }
        if($request->input('filter_phone')){
            $clients = $clients->where('phone', 'like', '%' . $request->input('filter_phone') . '%');
        }

        $clients = $clients->get();

        $request->flash();

        return view('clients.clientList', [
            'title' => 'Покупатели',
            'clients' => $clients
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = Client::findOrFail($id);
        $userInfo = $user->getCustomerInfo();

        if ($userInfo == null) {
            $userInfo = new \stdClass();
            $userInfo->photo = null;
            $userInfo->city = null;
            $userInfo->country = null;
            $userInfo->sex = null;
            $userInfo->birthday = null;
            $userInfo->car = null;
        }

        return view('clients.client', ['user' => $user, 'userInfo' => $userInfo]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return null;
    }

    /**
     * update resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update($id, Request $request)
    {
        $user = Client::findOrFail($id);

        $input = $request->all();

        $userInput = [
            'login' => $request->login,
            'email' => $request->email,
            'phone' => $request->phone
        ];

        $customerInfoinput = [
            'city' => $request->city,
            'sex' => $request->sex,
            'birthday' => $request->birthday,
            'car' => $request->car
        ];


        $user->fill($userInput)->save();
        $userInfo = $user->getCustomerInfo()->fill($customerInfoinput)->save();

        return redirect()->route('auth.customers.index');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        return null;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $master = Client::find($request->id);
        $master->getClientInfo()->forceDelete();
        $master->delete();

        return response()->json(['succses'=>'Удалено'], 200); 
    }
}
