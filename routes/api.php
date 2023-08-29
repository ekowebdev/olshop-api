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
    Route::get('/email/verify/{id}', '\App\Http\Controllers\API\v1\Auth\AuthController@verify')->name('verification.verify');
    Route::get('/email/notice', '\App\Http\Controllers\API\v1\Auth\AuthController@notice')->name('verification.notice');
	Route::group(['prefix' => '/v1/{locale}'], function(){
        // Auth
        Route::post('/register', '\App\Http\Controllers\API\v1\Auth\AuthController@register');
        Route::post('/login', '\App\Http\Controllers\API\v1\Auth\AuthController@login');
        Route::post('/refresh-token', '\App\Http\Controllers\API\v1\Auth\AuthController@refresh_token');
        Route::get('/email/resend', '\App\Http\Controllers\API\v1\Auth\AuthController@resend')->name('verification.resend');
        Route::group(['middleware' => ['auth:api']], function () {
            Route::group(['middleware' => ['role:admin']], function () {
                // User
                Route::get('/users', '\App\Http\Controllers\API\v1\UserController@index');
                Route::get('/users/{id}', '\App\Http\Controllers\API\v1\UserController@show');
                Route::post('/users', '\App\Http\Controllers\API\v1\UserController@store');
                Route::put('/users/{id}', '\App\Http\Controllers\API\v1\UserController@update');
                Route::patch('/users/{id}', '\App\Http\Controllers\API\v1\UserController@update');
                Route::delete('/users/{id}', '\App\Http\Controllers\API\v1\UserController@delete');
                // Item Gift
                Route::post('/gifts', '\App\Http\Controllers\API\v1\ItemGiftController@store');
                Route::put('/gifts/{id}', '\App\Http\Controllers\API\v1\ItemGiftController@update');
                Route::patch('/gifts/{id}', '\App\Http\Controllers\API\v1\ItemGiftController@update');
                Route::delete('/gifts/{id}', '\App\Http\Controllers\API\v1\ItemGiftController@delete');
                // Item Gift Image
                Route::post('/gifts/{itemGiftId}/images', '\App\Http\Controllers\API\v1\ItemGiftImageController@store');
                Route::delete('/gifts/{itemGiftId}/{imageName}/images', '\App\Http\Controllers\API\v1\ItemGiftImageController@delete');
            });
            Route::group(['middleware' => ['role:admin|customer']], function () {
                // Redeem Item Gift
                Route::get('/gifts/redeem', '\App\Http\Controllers\API\v1\RedeemController@index');
                Route::get('/gifts/redeem/{id}', '\App\Http\Controllers\API\v1\RedeemController@show');
                Route::post('/gifts/{itemGiftId}/redeem', '\App\Http\Controllers\API\v1\RedeemController@redeem');
                Route::post('/gifts/redeem', '\App\Http\Controllers\API\v1\RedeemController@redeem_multiple');
                // Wishlist Item Gift
                Route::get('/gifts/wishlist', '\App\Http\Controllers\API\v1\WishlistController@index');
                Route::post('/gifts/{itemGiftId}/wishlist', '\App\Http\Controllers\API\v1\WishlistController@wishlist');
                // Review Item Gift
                Route::get('/gifts/review', '\App\Http\Controllers\API\v1\ReviewController@index');
                Route::get('/gifts/review/{id}', '\App\Http\Controllers\API\v1\ReviewController@show');
                Route::post('/gifts/{itemGiftId}/rating', '\App\Http\Controllers\API\v1\ReviewController@rating');
            });
            Route::post('/logout', '\App\Http\Controllers\API\v1\Auth\AuthController@logout');
        });
        // Item Gift
        Route::get('/gifts', '\App\Http\Controllers\API\v1\ItemGiftController@index');
        Route::get('/gifts/{id}', '\App\Http\Controllers\API\v1\ItemGiftController@show');
    });
});
