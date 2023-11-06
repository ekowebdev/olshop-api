<?php

namespace App\Providers;

use App\Http\Models\ItemGift;
use App\Observers\ItemObserver;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        date_default_timezone_set('Asia/Jakarta');
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // ItemGift::observe(ItemObserver::class);
        Schema::defaultStringLength(191);
    }
}
