<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientToRate extends Model
{
    protected $table = 'client_to_rate';

    protected $fillable = [
        'client_id',
        'rate_id',
        'expires_at'
    ];
}
