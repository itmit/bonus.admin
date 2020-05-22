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
    public function index()
    {
        return view('clients.clientList', [
            'title' => 'Покупатели',
            'clients' => Client::where('type', 'customer')->get()
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
