<?php

namespace App\Http\Controllers\Web;

use App\Models\Client;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class BusinessmanWebController extends Controller
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

                $clients = Client::join('client_businessmen', 'clients.id', '=', 'client_businessmen.client_id')
                    ->where('type', 'businessman')
                    ->where('client_businessmen.city', $managerCity)
                    ->get();
            }
            else {
                $clients = Client::where('type', 'businessman')->get();
            }

            return view('businessmen.clientList', [
                'title' => 'Предприниматели',
                'clients' => $clients
            ]);
        }

        $clients = Client::query();

        if ($admin != 1) {
            $managerCity = Auth::user()->city;
            $clients->join('client_businessmen', 'clients.id', '=', 'client_businessmen.client_id');
            $clients->where('client_businessmen.city', $managerCity);
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
            'title' => 'Предприниматели',
            'clients' => $clients
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('departments.departmentCreate', [
            'title' => 'Создать отдел исполнителя'
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
        $userInfo = $user->getBusinessmenInfo();

        if ($userInfo == null) {
            $userInfo = new \stdClass();
            $userInfo->photo = null;
            $userInfo->city = null;
            $userInfo->country = null;
            $userInfo->sex = null;
            $userInfo->birthday = null;
            $userInfo->car = null;
        }

        return view('businessmen.client', ['user' => $user, 'userInfo' => $userInfo]);
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
            'address' => $request->address,
            'work_time' => $request->work_time,
            'contact' => $request->contact,
            'description' => $request->description,
            'vk' => $request->vk,
            'facebook' => $request->facebook,
            'instagram' => $request->instagram,
            'odnoklassniki' => $request->odnoklassniki
        ];


        $user->fill($userInput)->save();
        $userInfo = $user->getBusinessmenInfo()->fill($customerInfoinput)->save();

        return redirect()->route('auth.businessmen.index');
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
            'name' => 'required|min:3|max:191|string',
            'phone' => 'required|min:11|max:11',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:6',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('auth.departments.create')
                ->withErrors($validator)
                ->withInput();
        }

        $phone = request('phone');

        $phoneNumberUtil = \libphonenumber\PhoneNumberUtil::getInstance();
        $phoneNumberObject = $phoneNumberUtil->parse($phone, 'RU');
        $phone = $phoneNumberUtil->format($phoneNumberObject, \libphonenumber\PhoneNumberFormat::E164);

        $request->phone = $phone;

        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->password)
        ]);

        Department::create([
            'department_id' => $user->id,
            'uuid' => Str::uuid(),
            'name' => $request->input('name'),
            'phone' => $request->input('phone'),
            'rating' => 0,
        ]);

        return redirect()->route('auth.departments.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        Department::destroy($request->id);
        return response()->json(['succses'=>'Удалено'], 200); 
    }
}
