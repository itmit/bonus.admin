<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceItem extends Model
{
    protected $table = 'service_items';

    /**
     * @var array
     */
    protected $guarded = ['id'];
}
