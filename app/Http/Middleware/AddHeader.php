<?php namespace App\Http\Middleware;

use Closure;

class AddHeader
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        $response->header('Cache-Control', 'no-cache, must-revalidate');
        $response->header('Accept', 'application/json');
        $response->header('Content-Type', 'application/json');

        return $response;
    }
}