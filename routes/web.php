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

Route::get('/', function () {
    return view('welcome');
});

Route::get('/communities', '\App\Http\Controllers\CommunityController@index');

Route::get('/save-comm', '\App\Http\Controllers\CommunityController@save');

Route::get('/check-comm', '\App\Http\Controllers\CommunityController@check');

Route::get('/hello', function () {
    echo 'Hello world!';

});