<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\StockArchive;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ArchiveWebController extends Controller
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

            $stocks = StockArchive::where('city', $managerCity)->get();
        }
        else {
            $stocks = StockArchive::all();
        }


        return view('archives.archivesList', [
            'title' => 'Список акций в архиве',
            'stocks' => $stocks
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
        // $validator = Validator::make($request->all(), [
        //     'email' => 'required|email|unique:clients',
        //     'name' => 'required|min:3|max:191',
        //     'work' => 'required|array',
        //     'phone' => 'required',
        //     'password' => 'required|min:6|confirmed',
        //     'department' => 'required'
        // ]);

        // if ($validator->fails()) {
        //     return redirect()
        //         ->route('auth.masters.create')
        //         ->withErrors($validator)
        //         ->withInput();
        // }

        // $phone = request('phone');

        // $phoneNumberUtil = \libphonenumber\PhoneNumberUtil::getInstance();
        // $phoneNumberObject = $phoneNumberUtil->parse($phone, 'RU');
        // $phone = $phoneNumberUtil->format($phoneNumberObject, \libphonenumber\PhoneNumberFormat::E164);

        // $request->phone = $phone;

        // try {
        //     DB::transaction(function () use ($request) {
        //         $master = Client::create([
        //             'uuid' => Str::uuid(),
        //             'email' => $request->input('email'),
        //             'type' => 'master',
        //             'password' => Hash::make($request->password),
        //         ]);
        
        //         MasterInfo::create([
        //             'master_id' => $master->id,
        //             'department_id' => $request->department,
        //             'name' => $request->input('name'),
        //             'qualification' => '0',
        //             'work' => implode(';', $request->work),
        //             'phone' => $request->input('phone'),
        //             'rating' => 0,
        //             'status' => 'offline',
        //         ]);
        //     });
        // } catch (\Throwable $th) {
        //     dd($th);
        //     // return redirect()->route('auth.masters.create')->withErrors($th)->withInput();
        // }

        

        return redirect()->route('auth.masters.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        // $master = Client::find($request->id);
        // $master->getMasterInfo()->forceDelete();
        // $master->delete();

        // return response()->json(['succses'=>'Удалено'], 200); 
    }

    /**
     */
    public function show(Request $request, $id)
    {
        $master = News::where('id', $id)->first();
        return view('masters.masterDetail', [
            'master' => $master,
            'info' => $master->getMasterInfo(),
        ]); 
    }
}
