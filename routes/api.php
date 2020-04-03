<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('login', 'Api\AuthApiController@login');
Route::post('register', 'Api\AuthApiController@register');
Route::post('logout', 'Api\AuthApiController@logout');
Route::post('fillInfo', 'Api\AuthApiController@fillInfo');

Route::group(['middleware' => 'auth:api'], function(){

    Route::resource('service', 'Api\ServiceApiController');
    Route::post('service/getCustomerByUUID', 'Api\ServiceApiController@getCustomerByUUID');
    Route::post('service/searchCustomer', 'Api\ServiceApiController@search');

    Route::resource('businessmanservice', 'Api\BusinessmanServiceApiController');
    
    Route::resource('client', 'Api\ClientApiController');

    Route::resource('businessmanstock', 'Api\BusinessmanStockApiController');
    
});

Route::get('businessmanstock/test', 'Api\BusinessmanStockApiController@test');