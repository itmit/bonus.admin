<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dialog extends Model
{
    use SoftDeletes;
    
    protected $table = 'dialogs';

    /**
     * @var array
     */
    protected $guarded = ['id'];
}
