<?php

use App\Events\TestEvent;
use App\Http\Models\User;
use Illuminate\Support\Str;
use App\Http\Models\SearchLog;
use Aws\DynamoDb\DynamoDbClient;
use Illuminate\Support\Facades\Route;
use BaoPham\DynamoDb\Facades\DynamoDb;
use App\Notifications\RealTimeNotification;
use Aws\DynamoDb\Exception\DynamoDbException;

Route::middleware(['xssclean'])->group(function () {
    Route::get('/email/verify/{id}', '\App\Http\Controllers\API\v1\Auth\AuthController@verify')->name('verification.verify');
    Route::get('/reset/password/{token}', '\App\Http\Controllers\API\v1\Auth\AuthController@reset_password')->name('password.reset');
    Route::group(['prefix' => '/v1/{locale}'], function(){
        // Auth
        Route::post('/register', '\App\Http\Controllers\API\v1\Auth\AuthController@register');
        Route::post('/login', '\App\Http\Controllers\API\v1\Auth\AuthController@login');
        Route::post('/refresh-token', '\App\Http\Controllers\API\v1\Auth\AuthController@refresh_token');
        Route::post('/email/resend', '\App\Http\Controllers\API\v1\Auth\AuthController@resend')->name('verification.resend');
        Route::post('/forget/password', '\App\Http\Controllers\API\v1\Auth\AuthController@forget_password')->name('forget.password'); 
        Route::post('/reset/password', '\App\Http\Controllers\API\v1\Auth\AuthController@reset_password_update')->name('password.update');
        Route::group(['middleware' => ['auth:api']], function () {
            Route::group(['middleware' => ['role:admin']], function () {
                // User
                Route::get('/users', '\App\Http\Controllers\API\v1\UserController@index');
                Route::post('/users', '\App\Http\Controllers\API\v1\UserController@store');
                Route::delete('/users/{id}', '\App\Http\Controllers\API\v1\UserController@delete');
                // Category
                Route::post('/category', '\App\Http\Controllers\API\v1\CategoryController@store');
                Route::post('/category/{id}', '\App\Http\Controllers\API\v1\CategoryController@update');
                Route::delete('/category/{id}', '\App\Http\Controllers\API\v1\CategoryController@delete');
                // Brand
                Route::post('/brand', '\App\Http\Controllers\API\v1\BrandController@store');
                Route::post('/brand/{id}', '\App\Http\Controllers\API\v1\BrandController@update');
                Route::delete('/brand/{id}', '\App\Http\Controllers\API\v1\BrandController@delete');
                // Item Gift
                Route::post('/gifts', '\App\Http\Controllers\API\v1\ItemGiftController@store');
                Route::put('/gifts/{id}', '\App\Http\Controllers\API\v1\ItemGiftController@update');
                Route::patch('/gifts/{id}', '\App\Http\Controllers\API\v1\ItemGiftController@update');
                Route::delete('/gifts/{id}', '\App\Http\Controllers\API\v1\ItemGiftController@delete');
                // Item Gift Image
                Route::post('/gifts/images', '\App\Http\Controllers\API\v1\ItemGiftImageController@store');
                Route::post('/gifts/images/{id}', '\App\Http\Controllers\API\v1\ItemGiftImageController@update');
                Route::delete('/gifts/images/{id}', '\App\Http\Controllers\API\v1\ItemGiftImageController@delete');
                // Variant
                Route::post('/variants', '\App\Http\Controllers\API\v1\VariantController@store');
                Route::put('/variants/{id}', '\App\Http\Controllers\API\v1\VariantController@update');
                Route::delete('/variants/{id}', '\App\Http\Controllers\API\v1\VariantController@delete');
                // Payment Log
                Route::get('/payment-logs', '\App\Http\Controllers\API\v1\PaymentLogController@index');
                Route::get('/payment-logs/{id}', '\App\Http\Controllers\API\v1\PaymentLogController@show');
                // Shipping
                Route::get('/shippings', '\App\Http\Controllers\API\v1\ShippingController@index');
                Route::get('/shippings/{id}', '\App\Http\Controllers\API\v1\ShippingController@show');
                // RajaOngkir
                Route::get('/rajaongkir/province', '\App\Http\Controllers\API\v1\RajaOngkirController@getProvince');
                Route::get('/rajaongkir/city', '\App\Http\Controllers\API\v1\RajaOngkirController@getCity');
                Route::post('/rajaongkir/cost', '\App\Http\Controllers\API\v1\RajaOngkirController@getCost');
            });
            Route::group(['middleware' => ['role:admin|customer']], function () {
                // User
                Route::get('/users/{id}', '\App\Http\Controllers\API\v1\UserController@show');
                Route::put('/users/{id}', '\App\Http\Controllers\API\v1\UserController@update');
                Route::patch('/users/{id}', '\App\Http\Controllers\API\v1\UserController@update');
                Route::post('/users/main-address/{id}', '\App\Http\Controllers\API\v1\UserController@set_main_address');
                // Profile
                Route::get('/profile', '\App\Http\Controllers\API\v1\ProfileController@index');
                Route::get('/profile/{id}', '\App\Http\Controllers\API\v1\ProfileController@show');
                Route::post('/profile', '\App\Http\Controllers\API\v1\ProfileController@store');
                Route::post('/profile/{id}', '\App\Http\Controllers\API\v1\ProfileController@update');
                Route::delete('/profile/{id}', '\App\Http\Controllers\API\v1\ProfileController@delete');
                // Address
                Route::get('/address', '\App\Http\Controllers\API\v1\AddressController@index');
                Route::get('/address/{id}', '\App\Http\Controllers\API\v1\AddressController@show');
                Route::post('/address', '\App\Http\Controllers\API\v1\AddressController@store');
                Route::put('/address/{id}', '\App\Http\Controllers\API\v1\AddressController@update');
                Route::delete('/address/{id}', '\App\Http\Controllers\API\v1\AddressController@delete');
                // Redeem Item Gift
                Route::get('/gifts/redeem', '\App\Http\Controllers\API\v1\RedeemController@index');
                Route::get('/gifts/redeem/{id}', '\App\Http\Controllers\API\v1\RedeemController@show');
                Route::post('/gifts/redeem/checkout', '\App\Http\Controllers\API\v1\RedeemController@checkout')->middleware('verified');
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
                // Search Log
                Route::get('/search-logs', '\App\Http\Controllers\API\v1\SearchLogController@index');
                Route::post('/search-logs', '\App\Http\Controllers\API\v1\SearchLogController@store');
                Route::get('/search-logs/{id}', '\App\Http\Controllers\API\v1\SearchLogController@show');
                Route::get('/search-logs/user/{id}', '\App\Http\Controllers\API\v1\SearchLogController@showByUser');
                Route::put('/search-logs/{id}', '\App\Http\Controllers\API\v1\SearchLogController@update');
                Route::delete('/search-logs/{id}', '\App\Http\Controllers\API\v1\SearchLogController@delete');
            });
            Route::post('/logout', '\App\Http\Controllers\API\v1\Auth\AuthController@logout');
        });
        // Province
        Route::get('/province', '\App\Http\Controllers\API\v1\ProvinceController@index');
        Route::get('/province/{id}', '\App\Http\Controllers\API\v1\ProvinceController@show');
        // City
        Route::get('/city', '\App\Http\Controllers\API\v1\CityController@index');
        Route::get('/city/{id}', '\App\Http\Controllers\API\v1\CityController@show');
        // Subdistrict
        Route::get('/subdistrict', '\App\Http\Controllers\API\v1\SubdistrictController@index');
        Route::get('/subdistrict/{id}', '\App\Http\Controllers\API\v1\SubdistrictController@show');
        // Category
        Route::get('/category', '\App\Http\Controllers\API\v1\CategoryController@index');
        Route::get('/category/{id}', '\App\Http\Controllers\API\v1\CategoryController@show');
        Route::get('/category/slug/{slug}', '\App\Http\Controllers\API\v1\CategoryController@showBySlug');
        // Brand
        Route::get('/brand', '\App\Http\Controllers\API\v1\BrandController@index');
        Route::get('/brand/{id}', '\App\Http\Controllers\API\v1\BrandController@show');
        Route::get('/brand/slug/{slug}', '\App\Http\Controllers\API\v1\BrandController@showBySlug');
        // Item Gift Image
        Route::get('/gifts/images', '\App\Http\Controllers\API\v1\ItemGiftImageController@index');
        Route::get('/gifts/images/{id}', '\App\Http\Controllers\API\v1\ItemGiftImageController@show');
        // Item Gift
        Route::get('/gifts', '\App\Http\Controllers\API\v1\ItemGiftController@index');
        Route::get('/gifts/{id}', '\App\Http\Controllers\API\v1\ItemGiftController@show');
        Route::get('/gifts/slug/{slug}', '\App\Http\Controllers\API\v1\ItemGiftController@showBySlug');
        Route::get('/gifts/category/{slug}', '\App\Http\Controllers\API\v1\ItemGiftController@showByCategory');
        Route::get('/gifts/brand/{slug}', '\App\Http\Controllers\API\v1\ItemGiftController@showByBrand');
        // Variant
        Route::get('/variants', '\App\Http\Controllers\API\v1\VariantController@index');
        Route::get('/variants/{id}', '\App\Http\Controllers\API\v1\VariantController@show');
        // Webhook
        Route::post('/webhook/midtrans', '\App\Http\Controllers\API\v1\WebhookController@midtransHandler');
    });
});
