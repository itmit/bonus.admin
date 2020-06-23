<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfileView extends Model
{
    protected $table = 'profile_views';

    public static function createViewLog($profileId) {
        $profileView= new ProfileView();
        $profileView->profile_id = $profileId;
        $profileView->session_id = \Session::getId();
        $profileView->user_id = ( \Auth::check() ) ? \Auth::id() : null;
        $profileView->ip = \Request::getClientIp();
        $profileView->agent = \Request::header('User-Agent');
        $profileView->save();
    }
}
