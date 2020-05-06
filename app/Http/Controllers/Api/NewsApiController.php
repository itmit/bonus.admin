<?php

namespace App\Http\Controllers\Api;

use App\Models\News;
use App\Models\NewsImage;
use App\Http\Controllers\Controller;

class NewsApiController extends ApiBaseController
{
    public function index()
    {
        return $this->sendResponse(News::all()->toArray(), "News");
    }

    public function show($uuid)
    {
        return $this->sendResponse(NewsImage::where('news_id', '=', News::where('uuid', '=', $uuid)->first()->id)->get()->toArray(), "News images");
    }
}
