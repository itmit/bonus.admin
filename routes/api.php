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
    Route::post('service/storeServiceType', 'Api\ServiceApiController@storeServiceType');
    Route::post('service/storeServiceItem', 'Api\ServiceApiController@storeServiceItem');

    Route::resource('businessmanservice', 'Api\BusinessmanServiceApiController');
    
    Route::resource('client', 'Api\ClientApiController');

    Route::resource('businessmanstock', 'Api\BusinessmanStockApiController');
    Route::post('businessmanstock/{uuid}', 'Api\BusinessmanStockApiController@update');

    Route::resource('businessmanstockarchive', 'Api\BusinessmanStockArchiveApiController');
    Route::post('businessmanstockarchive/filtered', 'Api\BusinessmanStockArchiveApiController@filterStock');

    Route::resource('customerstock', 'Api\CustomerStockApiController');
    Route::post('customerstock/filtered', 'Api\CustomerStockApiController@filterStock');

    Route::resource('customerstockarchive', 'Api\CustomerStockArchiveApiController');
    Route::post('customerstockarchive/filtered', 'Api\CustomerStockArchiveApiController@filterStock');

    Route::resource('stockfilter', 'Api\StockFilterApiController');

});

Route::post('businessmanstock/test', 'Api\BusinessmanStockApiController@test');