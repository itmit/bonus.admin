<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientBusinessman extends Model
{
    protected $table = 'client_businessmen';

    /**
     * @var array
     */
    protected $guarded = ['id'];

    protected $username = 'name';
}
