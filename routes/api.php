<?php

use Illuminate\Http\Request;
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
Route::middleware(['xssclean'])->group(function () {
	Route::group(['prefix' => '/{locale}'], function(){
        // Auth
        Route::post('/login', '\App\Http\Controllers\AuthController@login');
        Route::post('/refresh-token', '\App\Http\Controllers\AuthController@refresh_token');
        Route::middleware(['auth:api'])->group(function () {
            Route::group(['middleware' => ['role:admin']], function () {
                // User
                Route::group(['prefix' => '/users'], function(){
                    Route::get('/', '\App\Http\Controllers\UserController@index');
                    Route::get('/{id}', '\App\Http\Controllers\UserController@show');
                    Route::post('/', '\App\Http\Controllers\UserController@store');
                    Route::put('/{id}', '\App\Http\Controllers\UserController@update');
                    Route::patch('/{id}', '\App\Http\Controllers\UserController@update');
                    Route::delete('/{id}', '\App\Http\Controllers\UserController@delete');
                });
            });
            // Item Gift
            Route::group(['prefix' => '/gifts'], function(){
                Route::middleware(['role:admin|customer'])->group(function ()  {
                    Route::get('/', '\App\Http\Controllers\ItemGiftController@index');
                    Route::get('/{id}', '\App\Http\Controllers\ItemGiftController@show');
                    Route::post('/{id}/redeem', '\App\Http\Controllers\ItemGiftController@redeem');
                    Route::post('/redeem', '\App\Http\Controllers\ItemGiftController@redeem_multiple');
                    Route::post('/{id}/wishlist', '\App\Http\Controllers\ItemGiftController@wishlist');
                    Route::post('/{id}/rating', '\App\Http\Controllers\ItemGiftController@rating');
                });
                Route::group(['middleware' => ['role:admin']], function () {
                    Route::post('/', '\App\Http\Controllers\ItemGiftController@store');
                    Route::put('/{id}', '\App\Http\Controllers\ItemGiftController@update');
                    Route::patch('/{id}', '\App\Http\Controllers\ItemGiftController@update');
                    Route::delete('/{id}', '\App\Http\Controllers\ItemGiftController@delete');
                });
            });
            Route::post('/logout', '\App\Http\Controllers\AuthController@logout');
        });
    });
});
