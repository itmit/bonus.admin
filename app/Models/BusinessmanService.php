<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BusinessmanService extends Model
{
    use SoftDeletes;
    
    protected $table = 'businessman_services';

    /**
     * @var array
     */
    protected $guarded = ['id'];
}
