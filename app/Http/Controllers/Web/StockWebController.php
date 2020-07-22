<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Stock;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class StockWebController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $admin = Auth::user()->adm;

        if ($admin != 1) {
            $managerCity = Auth::user()->city;

            $stocks = Stock::where('city', $managerCity)->get();
        }
        else {
            $stocks = Stock::all();
        }


        return view('stocks.stockList', [
            'title' => 'Акции',
            'stocks' => $stocks
        ]);
    }

    /**
     */
    public function show($id)
    {
        $stock = Stock::where('id', $id)->first();

        return view('stocks.stockDetail', [
            'stock' => $stock,
        ]); 
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
     * update resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update($id, Request $request)
    {
        $stock = Stock::findOrFail($id);

        $input = $request->all();

        $stock->fill($input)->save();

        return redirect()->route('auth.stocks.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $Stock = Stock::find($request->id);
        $Stock->delete();

        return response()->json(['succses'=>'Удалено'], 200); 
    }
}
