<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;

class Localization
{
    public function handle(Request $request, Closure $next)
    {
        $lang = Route::current()->parameter('locale');

        if($request->route('locale')) {
            $lang = $request->route('locale');
        }
        
        app()->setLocale($lang);

        return $next($request);
    }
}
