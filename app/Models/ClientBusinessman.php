<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClientBusinessman extends Model
{
    use SoftDeletes;
    
    protected $table = 'client_businessmen';

    /**
     * @var array
     */
    protected $guarded = ['id'];

    protected $username = 'name';
}
