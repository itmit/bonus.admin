<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientCustomer extends Model
{
    protected $table = 'client_customers';

    /**
     * @var array
     */
    protected $guarded = ['id'];

    protected $username = 'name';
}
