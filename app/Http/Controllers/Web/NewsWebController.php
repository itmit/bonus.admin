<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\News;
use App\Models\MasterInfo;
use App\Models\TypeOfWork;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use libphonenumber;

class NewsWebController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('news.newsList', [
            'title' => 'Список новостей',
            'news' => News::all()
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
        dd($request->all());

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
        $news = News::findOrFail($id);

        $input = $request->all();

        $news->fill($input)->save();

        return redirect()->route('auth.news.index');
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
    public function show($id)
    {
        $news = News::where('id', $id)->first();

        return view('news.newsDetail', [
            'news' => $news,
        ]); 
    }
}
