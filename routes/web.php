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

    Route::get('managers/create', 'Web\ManagerWebController@create')->name('managers.create');

    Route::apiResources([
        'customers' => 'Web\CustomerWebController',
        'businessmen' => 'Web\BusinessmanWebController',
        'stocks' => 'Web\StockWebController',
        'news' => 'Web\NewsWebController',
        'archives' => 'Web\ArchiveWebController',
        'services' => 'Web\ServiceWebController',
        'serviceTypes' => 'Web\ServiceTypeWebController',
        'managers' => 'Web\ManagerWebController',
    ]);
    
});

Auth::routes();

Route::get('/artisan-routes', function() {
    Artisan::call('route:list');
    dd(Artisan::output());
    return "route:list";
});

Route::get('/clear-cache', function() {
    Artisan::call('cache:clear');
    Artisan::call('config:cache');
    return "Cache is cleared";
});


Route::get('success', function() {
    return "authorization is succesful";
});