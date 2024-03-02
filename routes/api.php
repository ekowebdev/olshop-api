<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['xssclean'])->group(function () {
    // Email Verification
    Route::get('/email/verify/{id}', '\App\Http\Controllers\API\v1\Auth\AuthController@verify')->name('verification.verify');
    Route::group(['prefix' => '/v1/{locale}'], function(){
        // Auth
        Route::post('/register', '\App\Http\Controllers\API\v1\Auth\AuthController@register');
        Route::post('/login', '\App\Http\Controllers\API\v1\Auth\AuthController@login');
        Route::post('/login/system', '\App\Http\Controllers\API\v1\Auth\SystemAccessTokenController@token_system');
        Route::post('/refresh-token', '\App\Http\Controllers\API\v1\Auth\AuthController@refresh_token');
        // OAuth
        Route::post('/oauth/token', '\App\Http\Controllers\API\v1\Auth\AccessTokenController@issueToken');
        Route::post('/oauth/token/register', '\App\Http\Controllers\API\v1\Auth\AccessTokenController@issueTokenRegister');
        Route::post('/oauth/token/client', '\App\Http\Controllers\API\v1\Auth\AccessTokenController@issueTokenSystem');
        // Auth With Google
        Route::get('/auth/google', '\App\Http\Controllers\API\v1\Auth\AuthController@auth_google');
        Route::get('/auth/google/callback', '\App\Http\Controllers\API\v1\Auth\AuthController@auth_google_callback');
        // Email Verification
        Route::post('/email/resend', '\App\Http\Controllers\API\v1\Auth\AuthController@resend')->name('verification.resend');
        // Forgot Password
        Route::post('/forget/password', '\App\Http\Controllers\API\v1\Auth\AuthController@forget_password'); 
        Route::post('/reset/password', '\App\Http\Controllers\API\v1\Auth\AuthController@reset_password');
        // Webhook
        Route::post('/webhook/midtrans', '\App\Http\Controllers\API\v1\WebhookController@midtrans_handler');
        // Only User Authenticated
        Route::group(['middleware' => ['auth:api']], function () {
            // Only User Role Admin
            Route::group(['middleware' => ['role:admin']], function () {
                // User
                Route::group(['prefix' => '/users'], function(){
                    Route::get('/', '\App\Http\Controllers\API\v1\UserController@index');
                    Route::post('/', '\App\Http\Controllers\API\v1\UserController@store');
                    Route::delete('/{id}', '\App\Http\Controllers\API\v1\UserController@delete');
                });
                // Slider
                Route::group(['prefix' => '/sliders'], function(){
                    Route::post('/', '\App\Http\Controllers\API\v1\SliderController@store');
                    Route::post('/{id}', '\App\Http\Controllers\API\v1\SliderController@update');
                    Route::delete('/{id}', '\App\Http\Controllers\API\v1\SliderController@delete');
                });
                // Category
                Route::group(['prefix' => '/categories'], function(){
                    Route::post('/', '\App\Http\Controllers\API\v1\CategoryController@store');
                    Route::post('/{id}', '\App\Http\Controllers\API\v1\CategoryController@update');
                    Route::delete('/{id}', '\App\Http\Controllers\API\v1\CategoryController@delete');
                });
                // Brand
                Route::group(['prefix' => '/brands'], function(){
                    Route::post('/', '\App\Http\Controllers\API\v1\BrandController@store');
                    Route::post('/{id}', '\App\Http\Controllers\API\v1\BrandController@update');
                    Route::delete('/{id}', '\App\Http\Controllers\API\v1\BrandController@delete');
                });
                // Product
                Route::group(['prefix' => '/products'], function(){
                    // Product Image
                    Route::post('/images', '\App\Http\Controllers\API\v1\ProductImageController@store');
                    Route::post('/images/{id}', '\App\Http\Controllers\API\v1\ProductImageController@update');
                    Route::delete('/images/{id}', '\App\Http\Controllers\API\v1\ProductImageController@delete');
                    // Product
                    Route::post('/', '\App\Http\Controllers\API\v1\ProductController@store');
                    Route::put('/{id}', '\App\Http\Controllers\API\v1\ProductController@update');
                    Route::patch('/{id}', '\App\Http\Controllers\API\v1\ProductController@update');
                    Route::delete('/{id}', '\App\Http\Controllers\API\v1\ProductController@delete');
                });
                // Cart
                Route::get('/carts', '\App\Http\Controllers\API\v1\CartController@index');
                // Search Log
                Route::group(['prefix' => '/search-logs'], function(){
                    Route::get('/', '\App\Http\Controllers\API\v1\SearchLogController@index');
                    Route::get('/{id}', '\App\Http\Controllers\API\v1\SearchLogController@show');
                    Route::put('/{id}', '\App\Http\Controllers\API\v1\SearchLogController@update');
                });
                // Variant
                Route::group(['prefix' => '/variants'], function(){
                    Route::post('/', '\App\Http\Controllers\API\v1\VariantController@store');
                    Route::put('/{id}', '\App\Http\Controllers\API\v1\VariantController@update');
                    Route::delete('/{id}', '\App\Http\Controllers\API\v1\VariantController@delete');
                });
                // Payment Log
                Route::group(['prefix' => '/payment-logs'], function(){
                    Route::get('/', '\App\Http\Controllers\API\v1\PaymentLogController@index');
                    Route::get('/{id}', '\App\Http\Controllers\API\v1\PaymentLogController@show');
                });
                // Shipping
                Route::group(['prefix' => '/shippings'], function(){
                    Route::get('/', '\App\Http\Controllers\API\v1\ShippingController@index');
                    Route::get('/{id}', '\App\Http\Controllers\API\v1\ShippingController@show');
                    Route::put('/{id}', '\App\Http\Controllers\API\v1\ShippingController@update');
                });
                // Notification
                Route::put('/notifications/{id}', '\App\Http\Controllers\API\v1\NotificationController@update');
                // RajaOngkir
                Route::group(['prefix' => '/rajaongkir'], function(){
                    Route::get('/provinces', '\App\Http\Controllers\API\v1\Controller@getProvince');
                    Route::get('/cities', '\App\Http\Controllers\API\v1\RajaOngkirController@getCity');
                });
            });
            // Only User Role Admin/Customer
            Route::group(['middleware' => ['role:admin|customer']], function () {
                // User
                Route::group(['prefix' => '/users'], function(){
                    // Profile
                    Route::group(['prefix' => '/profiles'], function(){
                        Route::get('/', '\App\Http\Controllers\API\v1\ProfileController@index');
                        Route::get('/{id}', '\App\Http\Controllers\API\v1\ProfileController@show');
                        Route::post('/', '\App\Http\Controllers\API\v1\ProfileController@store');
                        Route::post('/{id}', '\App\Http\Controllers\API\v1\ProfileController@update');
                        Route::delete('/{id}', '\App\Http\Controllers\API\v1\ProfileController@delete');
                    });
                    // Address
                    Route::group(['prefix' => '/address'], function(){
                        Route::get('/', '\App\Http\Controllers\API\v1\AddressController@index');
                        Route::get('/{id}', '\App\Http\Controllers\API\v1\AddressController@show');
                        Route::post('/', '\App\Http\Controllers\API\v1\AddressController@store');
                        Route::put('/{id}', '\App\Http\Controllers\API\v1\AddressController@update');
                        Route::delete('/{id}', '\App\Http\Controllers\API\v1\AddressController@delete');
                    });
                    // User
                    Route::post('/main-address', '\App\Http\Controllers\API\v1\UserController@set_main_address');
                    Route::get('/{id}', '\App\Http\Controllers\API\v1\UserController@show');
                    Route::patch('/{id}', '\App\Http\Controllers\API\v1\UserController@update');
                });
                Route::group(['prefix' => '/products'], function(){
                    // Product
                    Route::get('/recomendations', '\App\Http\Controllers\API\v1\ProductController@showByUserRecomendation');
                    // Wishlist Product
                    Route::post('/{productId}/wishlists', '\App\Http\Controllers\API\v1\WishlistController@wishlist');
                });
                // Review 
                Route::group(['prefix' => '/reviews'], function(){
                    Route::post('/', '\App\Http\Controllers\API\v1\ReviewController@store');
                    Route::post('/bulk', '\App\Http\Controllers\API\v1\ReviewController@storeBulk');
                    Route::put('/{id}', '\App\Http\Controllers\API\v1\ReviewController@update');
                    Route::delete('/{id}', '\App\Http\Controllers\API\v1\ReviewController@delete');
                });
                // Order
                Route::group(['prefix' => '/orders'], function(){
                    Route::get('/', '\App\Http\Controllers\API\v1\OrderController@index');
                    Route::post('/checkout', '\App\Http\Controllers\API\v1\OrderController@checkout')->middleware('verified');
                    Route::post('/{id}/cancel', '\App\Http\Controllers\API\v1\OrderController@cancel')->middleware('verified');
                    Route::post('/{id}/receive', '\App\Http\Controllers\API\v1\OrderController@receive')->middleware('verified');
                    Route::get('/{id}', '\App\Http\Controllers\API\v1\OrderController@show');
                    Route::post('/', '\App\Http\Controllers\API\v1\OrderController@store')->middleware('verified');
                    Route::delete('/{id}', '\App\Http\Controllers\API\v1\OrderController@delete');
                });
                // Wishlist
                Route::group(['prefix' => '/wishlists'], function(){
                    Route::get('/', '\App\Http\Controllers\API\v1\WishlistController@index');
                    Route::get('/users/{userId}', '\App\Http\Controllers\API\v1\WishlistController@showByUser');
                    Route::get('/{id}', '\App\Http\Controllers\API\v1\WishlistController@show');
                });
                // Cart
                Route::group(['prefix' => '/carts'], function(){
                    Route::post('/', '\App\Http\Controllers\API\v1\CartController@store');
                    Route::get('/users/{userId}', '\App\Http\Controllers\API\v1\CartController@showByUser');
                    Route::get('/{id}', '\App\Http\Controllers\API\v1\CartController@show');
                    Route::put('/{id}', '\App\Http\Controllers\API\v1\CartController@update');
                    Route::delete('/{id}', '\App\Http\Controllers\API\v1\CartController@delete');
                });
                // Search Log
                Route::group(['prefix' => '/search-logs'], function(){
                    Route::post('/', '\App\Http\Controllers\API\v1\SearchLogController@store');
                    Route::get('/users/{userId}', '\App\Http\Controllers\API\v1\SearchLogController@showByUser');
                    Route::delete('/{id}', '\App\Http\Controllers\API\v1\SearchLogController@delete');
                });
                // Notification
                Route::group(['prefix' => '/notifications'], function(){
                    Route::get('/', '\App\Http\Controllers\API\v1\NotificationController@index');
                    Route::get('/users/{userId}', '\App\Http\Controllers\API\v1\NotificationController@showByUser');
                    Route::get('/{id}', '\App\Http\Controllers\API\v1\NotificationController@show');
                    Route::delete('/{id}', '\App\Http\Controllers\API\v1\NotificationController@delete');
                });
                // RajaOngkir Cek Ongkir
                Route::post('/rajaongkir/checking-costs', '\App\Http\Controllers\API\v1\RajaOngkirController@getCost');
                // Binderbyte Lacak Resi
                Route::post('/binderbyte/tracking-receipts', '\App\Http\Controllers\API\v1\TrackResiController@track');
            });
            // Logout
            Route::post('/logout', '\App\Http\Controllers\API\v1\Auth\AuthController@logout');
        });
        // All User
        // Slider
        Route::group(['prefix' => '/sliders'], function(){
            Route::get('/', '\App\Http\Controllers\API\v1\SliderController@index');
            Route::get('/active', '\App\Http\Controllers\API\v1\SliderController@showByActive');
            Route::get('/{id}', '\App\Http\Controllers\API\v1\SliderController@show');
        });
        // Province
        Route::group(['prefix' => '/provinces'], function(){
            Route::get('/', '\App\Http\Controllers\API\v1\ProvinceController@index');
            Route::get('/{id}', '\App\Http\Controllers\API\v1\ProvinceController@show');
        });
        // City
        Route::group(['prefix' => '/cities'], function(){
            Route::get('/', '\App\Http\Controllers\API\v1\CityController@index');
            Route::get('/{id}', '\App\Http\Controllers\API\v1\CityController@show');
        });
        // Subdistrict
        Route::group(['prefix' => '/subdistricts'], function(){
            Route::get('/', '\App\Http\Controllers\API\v1\SubdistrictController@index');
            Route::get('/{id}', '\App\Http\Controllers\API\v1\SubdistrictController@show');
        });
        // Category
        Route::group(['prefix' => '/categories'], function(){
            Route::get('/', '\App\Http\Controllers\API\v1\CategoryController@index');
            Route::get('/slugs/{slug}', '\App\Http\Controllers\API\v1\CategoryController@showBySlug');
            Route::get('/{id}', '\App\Http\Controllers\API\v1\CategoryController@show');
        });
        // Brand
        Route::group(['prefix' => '/brands'], function(){
            Route::get('/', '\App\Http\Controllers\API\v1\BrandController@index');
            Route::get('/slugs/{slug}', '\App\Http\Controllers\API\v1\BrandController@showBySlug');
            Route::get('/{id}', '\App\Http\Controllers\API\v1\BrandController@show');
        });
        // Product
        Route::group(['prefix' => '/products'], function(){
            // Product Image
            Route::get('/images', '\App\Http\Controllers\API\v1\ProductImageController@index');
            Route::get('/images/{id}', '\App\Http\Controllers\API\v1\ProductImageController@show');
            // Product
            Route::get('/', '\App\Http\Controllers\API\v1\ProductController@index');
            Route::get('/slugs/{slug}', '\App\Http\Controllers\API\v1\ProductController@showBySlug');
            Route::get('/categories/{categorySlug}', '\App\Http\Controllers\API\v1\ProductController@showByCategory');
            Route::get('/brands/{brandSlug}', '\App\Http\Controllers\API\v1\ProductController@showByBrand');
            Route::get('/{id}', '\App\Http\Controllers\API\v1\ProductController@show');
        });
        // Review
        Route::group(['prefix' => '/reviews'], function(){
            Route::get('/', '\App\Http\Controllers\API\v1\ReviewController@index');
            Route::get('/{id}', '\App\Http\Controllers\API\v1\ReviewController@show');
        });
        // Variant
        Route::group(['prefix' => '/variants'], function(){
            Route::get('/', '\App\Http\Controllers\API\v1\VariantController@index');
            Route::get('/slugs/{slug}', '\App\Http\Controllers\API\v1\VariantController@showBySlug');
            Route::get('/{id}', '\App\Http\Controllers\API\v1\VariantController@show');
        });
    });
});
