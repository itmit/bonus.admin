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
        if ( empty($request->all()) ) {
            return view('clients.clientList', [
                'title' => 'Предприниматели',
                'clients' => Client::where('type', 'businessman')->get()
            ]);
        }

        $clients = Client::query();

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
