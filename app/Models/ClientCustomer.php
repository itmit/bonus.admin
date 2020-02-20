<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClientCustomer extends Model
{
    use SoftDeletes;
    
    protected $table = 'client_customers';

    /**
     * @var array
     */
    protected $guarded = ['id'];

    protected $username = 'name';
}
