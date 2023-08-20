<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RedeemController;

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
        Route::group(['middleware' => ['auth:api']], function () {
            Route::group(['middleware' => ['role:admin']], function () {
                // User
                Route::get('/users', '\App\Http\Controllers\UserController@index');
                Route::get('/users/{id}', '\App\Http\Controllers\UserController@show');
                Route::post('/users', '\App\Http\Controllers\UserController@store');
                Route::put('/users/{id}', '\App\Http\Controllers\UserController@update');
                Route::patch('/users/{id}', '\App\Http\Controllers\UserController@update');
                Route::delete('/users/{id}', '\App\Http\Controllers\UserController@delete');
                // Item Gift
                Route::post('/gifts', '\App\Http\Controllers\ItemGiftController@store');
                Route::put('/gifts/{id}', '\App\Http\Controllers\ItemGiftController@update');
                Route::patch('/gifts/{id}', '\App\Http\Controllers\ItemGiftController@update');
                Route::delete('/gifts/{id}', '\App\Http\Controllers\ItemGiftController@delete');
            });
            Route::group(['middleware' => ['role:admin|customer']], function () {
                // Redeem Item Gift
                Route::get('/gifts/redeem', '\App\Http\Controllers\RedeemController@index');
                Route::get('/gifts/redeem/{id}', '\App\Http\Controllers\RedeemController@show');
                Route::post('/gifts/{itemGiftId}/redeem', '\App\Http\Controllers\RedeemController@redeem');
                Route::post('/gifts/redeem', '\App\Http\Controllers\RedeemController@redeem_multiple');
                // Wishlist Item Gift
                Route::get('/gifts/wishlist', '\App\Http\Controllers\WishlistController@index');
                Route::post('/gifts/{itemGiftId}/wishlist', '\App\Http\Controllers\WishlistController@wishlist');
                // Review Item Gift
                Route::get('/gifts/rating', '\App\Http\Controllers\ReviewController@index');
                Route::get('/gifts/rating/{id}', '\App\Http\Controllers\ReviewController@show');
                Route::post('/gifts/{itemGiftId}/rating', '\App\Http\Controllers\ReviewController@rating');
            });
            Route::post('/logout', '\App\Http\Controllers\AuthController@logout');
        });
        // Item Gift
        Route::get('/gifts', '\App\Http\Controllers\ItemGiftController@index');
        Route::get('/gifts/{id}', '\App\Http\Controllers\ItemGiftController@show');
    });
});
