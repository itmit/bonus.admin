<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerService extends Model
{
    use SoftDeletes;
    
    protected $table = 'customer_services';

    /**
     * @var array
     */
    protected $guarded = ['id'];
}
