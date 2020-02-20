<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceItem extends Model
{
    use SoftDeletes;
    
    protected $table = 'service_items';

    /**
     * @var array
     */
    protected $guarded = ['id'];
}
