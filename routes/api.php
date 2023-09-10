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
    // Route::get('/email/notice', '\App\Http\Controllers\API\v1\Auth\AuthController@notice')->name('verification.notice');
    Route::get('/reset/password/{token}', '\App\Http\Controllers\API\v1\Auth\AuthController@reset_password')->name('password.reset');
    Route::group(['prefix' => '/v1/{locale}'], function(){
        // Auth
        Route::post('/register', '\App\Http\Controllers\API\v1\Auth\AuthController@register');
        Route::post('/login', '\App\Http\Controllers\API\v1\Auth\AuthController@login');
        Route::post('/refresh-token', '\App\Http\Controllers\API\v1\Auth\AuthController@refresh_token');
        Route::get('/email/resend', '\App\Http\Controllers\API\v1\Auth\AuthController@resend')->name('verification.resend');
        
        Route::post('/forget/password', '\App\Http\Controllers\API\v1\Auth\AuthController@forget_password')->name('forget.password'); 
        Route::post('/reset/password', '\App\Http\Controllers\API\v1\Auth\AuthController@reset_password_update')->name('password.update');
        
        Route::group(['middleware' => ['auth:api','verified']], function () {
            Route::group(['middleware' => ['role:admin']], function () {
                // User
                Route::get('/users', '\App\Http\Controllers\API\v1\UserController@index');
                Route::get('/users/{id}', '\App\Http\Controllers\API\v1\UserController@show');
                Route::post('/users', '\App\Http\Controllers\API\v1\UserController@store');
                Route::put('/users/{id}', '\App\Http\Controllers\API\v1\UserController@update');
                Route::patch('/users/{id}', '\App\Http\Controllers\API\v1\UserController@update');
                Route::delete('/users/{id}', '\App\Http\Controllers\API\v1\UserController@delete');
                // Category
                Route::post('/category', '\App\Http\Controllers\API\v1\CategoryController@store');
                Route::put('/category/{id}', '\App\Http\Controllers\API\v1\CategoryController@update');
                Route::delete('/category/{id}', '\App\Http\Controllers\API\v1\CategoryController@delete');
                // Brand
                Route::post('/brand', '\App\Http\Controllers\API\v1\BrandController@store');
                Route::put('/brand/{id}', '\App\Http\Controllers\API\v1\BrandController@update');
                Route::delete('/brand/{id}', '\App\Http\Controllers\API\v1\BrandController@delete');
                // Item Gift
                Route::post('/gifts', '\App\Http\Controllers\API\v1\ItemGiftController@store');
                Route::put('/gifts/{id}', '\App\Http\Controllers\API\v1\ItemGiftController@update');
                Route::patch('/gifts/{id}', '\App\Http\Controllers\API\v1\ItemGiftController@update');
                Route::delete('/gifts/{id}', '\App\Http\Controllers\API\v1\ItemGiftController@delete');
                // Item Gift Image
                Route::post('/gifts/{itemGiftId}/images', '\App\Http\Controllers\API\v1\ItemGiftImageController@store');
                Route::delete('/gifts/{itemGiftId}/{imageName}/images', '\App\Http\Controllers\API\v1\ItemGiftImageController@delete');
                // Variant
                Route::post('/variants', '\App\Http\Controllers\API\v1\VariantController@store');
                Route::put('/variants/{id}', '\App\Http\Controllers\API\v1\VariantController@update');
                Route::delete('/variants/{id}', '\App\Http\Controllers\API\v1\VariantController@delete');
                // Payment Log
                Route::get('/payment-logs', '\App\Http\Controllers\API\v1\PaymentLogController@index');
                Route::get('/payment-logs/{id}', '\App\Http\Controllers\API\v1\PaymentLogController@show');
            });
            Route::group(['middleware' => ['role:admin|customer']], function () {
                // Address
                Route::get('/address', '\App\Http\Controllers\API\v1\AddressController@index');
                Route::get('/address/{id}', '\App\Http\Controllers\API\v1\AddressController@show');
                Route::post('/address', '\App\Http\Controllers\API\v1\AddressController@store');
                Route::put('/address/{id}', '\App\Http\Controllers\API\v1\AddressController@update');
                Route::delete('/address/{id}', '\App\Http\Controllers\API\v1\AddressController@delete');
                // Redeem Item Gift
                Route::get('/gifts/redeem', '\App\Http\Controllers\API\v1\RedeemController@index');
                Route::get('/gifts/redeem/{id}', '\App\Http\Controllers\API\v1\RedeemController@show');
                Route::post('/gifts/{itemGiftId}/redeem', '\App\Http\Controllers\API\v1\RedeemController@redeem');
                Route::post('/gifts/redeem', '\App\Http\Controllers\API\v1\RedeemController@redeem_multiple');
                Route::delete('/gifts/redeem/{id}', '\App\Http\Controllers\API\v1\RedeemController@delete');
                // Wishlist Item Gift
                Route::get('/gifts/wishlist', '\App\Http\Controllers\API\v1\WishlistController@index');
                Route::post('/gifts/{itemGiftId}/wishlist', '\App\Http\Controllers\API\v1\WishlistController@wishlist');
                // Review Item Gift
                Route::get('/gifts/review', '\App\Http\Controllers\API\v1\ReviewController@index');
                Route::get('/gifts/review/{id}', '\App\Http\Controllers\API\v1\ReviewController@show');
                Route::post('/gifts/{itemGiftId}/rating', '\App\Http\Controllers\API\v1\ReviewController@rating');
                // Cart
                Route::get('/carts', '\App\Http\Controllers\API\v1\CartController@index');
                Route::post('/carts', '\App\Http\Controllers\API\v1\CartController@store');
                Route::get('/carts/{id}', '\App\Http\Controllers\API\v1\CartController@show');
                Route::delete('/carts/{id}', '\App\Http\Controllers\API\v1\CartController@delete');
            });
            Route::post('/logout', '\App\Http\Controllers\API\v1\Auth\AuthController@logout');
        });
        // Category
        Route::get('/category', '\App\Http\Controllers\API\v1\CategoryController@index');
        Route::get('/category/{id}', '\App\Http\Controllers\API\v1\CategoryController@show');
        // Brand
        Route::get('/brand', '\App\Http\Controllers\API\v1\BrandController@index');
        Route::get('/brand/{id}', '\App\Http\Controllers\API\v1\BrandController@show');
        // Item Gift
        Route::get('/gifts', '\App\Http\Controllers\API\v1\ItemGiftController@index');
        Route::get('/gifts/{id}', '\App\Http\Controllers\API\v1\ItemGiftController@show');
        // Variant
        Route::get('/variants', '\App\Http\Controllers\API\v1\VariantController@index');
        Route::get('/variants/{id}', '\App\Http\Controllers\API\v1\VariantController@show');
        // Webhook
        Route::post('/webhook/midtrans', '\App\Http\Controllers\API\v1\WebhookController@midtransHandler');
    });
});
