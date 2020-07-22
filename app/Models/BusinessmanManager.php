<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BusinessmanManager extends Model
{
    use SoftDeletes;
    
    protected $table = 'businessman_manager';

    protected $fillable = [
        'client_id',
        'businessman_id'
    ];
}
