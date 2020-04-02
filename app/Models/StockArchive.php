<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockArchive extends Model
{
    protected $table = 'stock_archives';

    /**
     * @var array
     */
    protected $guarded = ['id'];
}
