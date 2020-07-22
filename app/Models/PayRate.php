<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayRate extends Model
{
    protected $table = 'pay_rate';

    protected $fillable = [
        'client_id',
        'rate_id',
        'payment_id',
        'count_rates'
    ];
}
