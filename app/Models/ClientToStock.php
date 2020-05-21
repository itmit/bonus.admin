<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientToStock extends Model
{
    protected $table = 'client_to_stock';

    /**
     * @var array
     */
    protected $guarded = ['id'];
}
