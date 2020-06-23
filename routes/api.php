<?php

use Illuminate\Support\Facades\Route;


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
Route::post('authorizationAnExternalService', 'Api\AuthApiController@authorizationAnExternalService');

Route::group(['middleware' => 'auth:api'], function(){

    Route::resource('service', 'Api\ServiceApiController');
    Route::post('service/getCustomerByUUID', 'Api\ServiceApiController@getCustomerByUUID');
    Route::post('service/searchCustomer', 'Api\ServiceApiController@search');
    Route::post('service/storeServiceType', 'Api\ServiceApiController@storeServiceType');
    Route::post('service/storeServiceItem', 'Api\ServiceApiController@storeServiceItem');
    Route::post('service/removeServiceItem', 'Api\ServiceApiController@removeServiceItem');
    Route::get('getAllServices', 'Api\ServiceApiController@getAllServices');
    Route::get('getMyBonuses', 'Api\ServiceApiController@getMyBonuses');

    Route::resource('businessmanservice', 'Api\BusinessmanServiceApiController');
    
    Route::resource('client', 'Api\ClientApiController');
    Route::post('client/update', 'Api\ClientApiController@update');
    Route::post('subscribeToBusinessman', 'Api\ClientApiController@subscribeToBusinessman');
    Route::post('unsubscribeToBusinessman', 'Api\ClientApiController@unsubscribeToBusinessman');
    Route::get('getSubscriptuions', 'Api\ClientApiController@getSubscriptuions');

    Route::resource('businessmanstock', 'Api\BusinessmanStockApiController');
    Route::post('businessmanstock/{uuid}', 'Api\BusinessmanStockApiController@update');

    Route::resource('businessmanstockarchive', 'Api\BusinessmanStockArchiveApiController');
    Route::post('businessmanstockarchive/filtered', 'Api\BusinessmanStockArchiveApiController@filterStock');

    Route::resource('customerstock', 'Api\CustomerStockApiController');
    Route::post('customerstock/filtered', 'Api\CustomerStockApiController@filterStock');
    Route::post('addStockToFavorite', 'Api\CustomerStockApiController@addToFavorite');
    Route::get('getFavoriteStocks', 'Api\CustomerStockApiController@getFavoriteStocks');

    Route::resource('customerstockarchive', 'Api\CustomerStockArchiveApiController');
    Route::post('customerstockarchive/filtered', 'Api\CustomerStockArchiveApiController@filterStock');

    Route::resource('stockfilter', 'Api\StockFilterApiController');

    Route::resource('portfolio', 'Api\PortfolioController');

    Route::resource('news', 'Api\NewsApiController');

    Route::resource('dialogs', 'Api\DialogApiController');
    Route::post('sendMessage', 'Api\DialogApiController@sendMessage');

    Route::get('statistics/getAgeStatistics', 'Api\StatisticsApiController@getAgeStatistics');
    Route::get('statistics/getGeographyStatistics', 'Api\StatisticsApiController@getGeographyStatistics');
    Route::get('statistics/getSalesStatistics', 'Api\StatisticsApiController@getSalesStatistics');
    Route::get('statistics/getProfileViewsStatistics', 'Api\StatisticsApiController@getProfileViewsStatistics');
    Route::get('statistics/getStockViewsStatistics', 'Api\StatisticsApiController@getStockViewsStatistics');
});

Route::post('businessmanstock/test', 'Api\BusinessmanStockApiController@test');