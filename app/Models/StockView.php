<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockView extends Model
{
    protected $table = 'stock_views';

    public static function createViewLog($stockId) {
        $stockView= new StockView();
        $stockView->stock_id = $stockId;
        $stockView->session_id = \Session::getId();
        $stockView->user_id = ( \Auth::check() ) ? \Auth::id() : null;
        $stockView->ip = \Request::getClientIp();
        $stockView->agent = \Request::header('User-Agent');
        $stockView->save();
    }
}
