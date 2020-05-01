<?php

namespace App\Http\Controllers\Api;

use App\Models\Client;
use App\Models\Portfolio;
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

class PortfolioController extends ApiBaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $portfolio = Portfolio::where('client_id', auth('api')->user()->id)->get(['uuid', 'file'])->toArray();
        return $this->sendResponse($portfolio,'');
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
            'photo' => 'image|mimes:jpeg,jpg,png,gif|max:10000',
        ]);

        if ($validator->fails()) { 
            return response()->json(['errors'=>$validator->errors()], 400);            
        }

        $path = $request->photo->store('public/portfolio');
        $url = Storage::url($path);
        Portfolio::create([
            'uuid' => Str::uuid(),
            'client_id' => auth('api')->user()->id,
            'file' => $url
        ]);
        return $this->sendResponse([],'Stored');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($uuid)
    {
        Portfolio::where('client_id', auth('api')->user()->id)->where('uuid', $uuid)->delete();
        return $this->sendResponse([],'Deleted');
    }
}
