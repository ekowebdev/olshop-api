<?php

namespace App\Http\Middleware;

use App;
use Closure;
use Illuminate\Support\Facades\Config;
use App\Exceptions\AuthenticationException;
use App\Exceptions\ForbiddenException;

class UserMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if(!auth()->guard('api')->user()) throw new AuthenticationException();

        if(auth()->guard('api')->check()) {
            if(!empty(auth()->guard('api')->user())){
                throw new ForbiddenException(trans('error.not_authorize_user'));
            }
        } else {
            throw new ForbiddenException(trans('error.not_authorize_user'));
        }

        Config::set('setting.user', auth()->guard('api')->user());

        return $next($request);
    }
}
