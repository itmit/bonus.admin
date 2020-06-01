<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group(['as' => 'auth.', 'middleware' => 'auth'], function () {
    
    Route::view('/', 'main');

    Route::apiResources([
        'customers' => 'Web\CustomerWebController',
        'businessmen' => 'Web\BusinessmanWebController',
        'stocks' => 'Web\StockWebController',
        'news' => 'Web\NewsWebController',
        'archives' => 'Web\ArchiveWebController',
        'services' => 'Web\ServiceWebController',
        'serviceTypes' => 'Web\ServiceTypeWebController',
    ]);
    
});

Auth::routes();