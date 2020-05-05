<?php

namespace App\Http\Controllers\Api;

use App\Models\News;
use App\Http\Controllers\Controller;

class NewsApiController extends ApiBaseController
{
    public function index()
    {
        return $this->sendResponse(News::all()->toArray(), "News");
    }
}
