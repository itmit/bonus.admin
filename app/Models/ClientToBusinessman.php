<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClientToBusinessman extends Model
{
    use SoftDeletes;
    
    protected $table = 'client_to_businessman';

    /**
     * @var array
     */
    protected $guarded = ['id'];

    protected $username = 'name';
}
