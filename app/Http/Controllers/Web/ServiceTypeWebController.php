<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ServiceType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ServiceTypeWebController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('serviceTypes.typesList', [
            'title' => 'Список видов услуг',
            'types' => ServiceType::all()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // return view('masters.masterCreate', [
        //     'title' => 'Создать мастера',
        //     'departments' => Department::all()->sortByDesc('rating'),
        //     'works' => TypeOfWork::all(),
        // ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        return redirect()->route('auth.ServiceTypes.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        ServiceType::destroy($request->id);

        return response()->json(['succses'=>'Удалено'], 200); 
    }

    /**
     */
    public function show(Request $request, $id)
    {
        $type = ServiceType::where('id', $id)->first();
        return view('serviceTypes.typeDetail', [
            'type' => $type,
        ]); 
    }
}