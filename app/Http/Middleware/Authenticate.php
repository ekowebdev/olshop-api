<?php

namespace App\Http\Middleware;

use Closure;
use App\Exceptions\AuthenticationException;
use Illuminate\Support\Facades\Config;
use Illuminate\Contracts\Auth\Factory as Auth;

class Authenticate
{
    /**
     * The authentication guard factory instance.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;

    /**
     * Create a new middleware instance.
     *
     * @param  \Illuminate\Contracts\Auth\Factory  $auth
     * @return void
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        $isGuest = Config::get('setting.guest');
        $user = $this->auth->guard('api')->user();
        // $user = $this->auth->guard('web')->user();

        if (empty($user) AND $isGuest === false) {
            throw new AuthenticationException();
        }

        Config::set('setting.user', $user);

        return $next($request);
    }
}
