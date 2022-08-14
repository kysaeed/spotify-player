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
    Route::post('device/list', 'PlayerController@device')->name('device.list');
    Route::post('state', 'PlayerController@state')->name('state');

    Route::get('track-info', 'PlayerController@trackInfo')->name('track-info');
    Route::get('audio-analysis/{idTrack}', 'PlayerController@audioAnaysis')->name('audio-analysis');

    Route::post('player/play', 'PlayerController@play')->name('player.play');
    Route::post('player/next', 'PlayerController@next')->name('player.next');
    Route::post('player/prev', 'PlayerController@prev')->name('player.prev');

});
