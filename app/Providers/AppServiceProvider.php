<?php

namespace App\Providers;

use App\Http\Models\Product;
use App\Observers\ItemObserver;
use App\Resolvers\SocialUserResolver;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Coderello\SocialGrant\Resolvers\SocialUserResolverInterface;
use Illuminate\Support\Facades\Response;

class AppServiceProvider extends ServiceProvider
{
    public $bindings = [
        SocialUserResolverInterface::class => SocialUserResolver::class,
    ];
       
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        date_default_timezone_set('Asia/Jakarta'); 
        
        Response::macro('api', function ($message = null, $data = [], $statusCode = 200) {
            if($message != null) {
                if(!empty($data) || $data != []){
                    return response()->json([
                        'message' => $message,
                        'data' => $data,
                        'status_code' => $statusCode,
                        'error' => 0
                    ], $statusCode);
                } else {
                    return response()->json([
                        'message' => $message,
                        'status_code' => $statusCode,
                        'error' => 0
                    ], $statusCode);
                }
            } else {
                return response()->json([
                    'data' => $data,
                    'status_code' => $statusCode,
                    'error' => 0
                ], $statusCode);
            }
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Product::observe(ItemObserver::class);
        Schema::defaultStringLength(191);
    }
}
