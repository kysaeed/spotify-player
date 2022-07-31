<?php

use Illuminate\Support\Facades\Route;

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

Route::group(['namespace' => 'App\Http\Controllers'], function() {

    Route::get('/', 'PlayerController@index')->name('top');
    Route::get('guest', 'PlayerController@guest')->name('guest');
    Route::post('access-token', 'LoginControler@accessToken')->name('access-token');

    Route::get('login', 'LoginControler@login')->name('login');
    Route::get('callback', 'LoginControler@callback')->name('callback');

    Route::post('device', 'PlayerController@device')->name('device');
    Route::get('track-info', 'PlayerController@trackInfo')->name('track-info');

});
