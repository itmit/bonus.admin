<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
    use SoftDeletes;
    
    protected $table = 'messages';

    /**
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * Get the dialog that the message belongs to.
     */
    public function dialog()
    {
        return $this->belongsTo('App\Models\Dialog');
    }
}
