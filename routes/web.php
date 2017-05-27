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

Route::get('/', '\App\Http\Controllers\Auth\LoginController@showLoginForm');
Route::get('/communities', '\App\Http\Controllers\CommunityController@index');
Route::get('/ajax', '\App\Http\Controllers\CommunityController@ajax');

Route::get('/save-comm', '\App\Http\Controllers\CommunityController@save');
Route::get('/add_monitor', '\App\Http\Controllers\CommunityController@addmonitor');

Route::get('/check-comm', '\App\Http\Controllers\CommunityController@check');

Route::get('/hello', function () {
    echo 'Hello world!';

});
Auth::routes();
Route::get('/vk_redirect', '\App\Http\Controllers\Auth\LoginController@vkRedirect');
Route::get('/home', 'HomeController@index');
