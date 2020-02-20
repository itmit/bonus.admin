<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClientBalance extends Model
{
    use SoftDeletes;
    
    protected $table = 'client_balances';

    /**
     * @var array
     */
    protected $guarded = ['id'];

    protected $username = 'name';
}
