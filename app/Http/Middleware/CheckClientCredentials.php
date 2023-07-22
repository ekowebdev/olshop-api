<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Config;
use Illuminate\Auth\AuthenticationException;
use Laravel\Passport\Exceptions\MissingScopeException;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\ResourceServer;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Nyholm\Psr7\Factory\Psr17Factory;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;

class CheckClientCredentials
{
    /**
     * The Resource Server instance.
     *
     * @var \League\OAuth2\Server\ResourceServer
     */
    protected $server;

    /**
     * Create a new middleware instance.
     *
     * @param  \League\OAuth2\Server\ResourceServer  $server
     * @return void
     */
    public function __construct(ResourceServer $server)
    {
        $this->server = $server;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  mixed  ...$scopes
     * @return mixed
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function handle($request, Closure $next, ...$scopes)
    {
        try {
            $psr17Factory = new Psr17Factory;

            $psr = (new PsrHttpFactory($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory))->createRequest($request);
            $psr = $this->server->validateAuthenticatedRequest($psr);
            // $whiteListClientId = ['18'];
            // $clientId = $psr->getAttribute('oauth_client_id');
            // if(!in_array($clientId,$whiteListClientId)){
            //     throw new AuthenticationException();
            // }
        } catch (OAuthServerException $e) {
            throw new AuthenticationException;
        }
        
        Config::set('setting.guest', true);

        $this->validateScopes($psr, $scopes);

        return $next($request);
    }

    /**
     * Validate the scopes on the incoming request.
     *
     * @param  \Psr\Http\Message\ServerRequestInterface $psr
     * @param  array  $scopes
     * @return void
     * @throws \Laravel\Passport\Exceptions\MissingScopeException
     */
    protected function validateScopes($psr, $scopes)
    {
        if (in_array('*', $tokenScopes = $psr->getAttribute('oauth_scopes'))) {
            return;
        }

        foreach ($scopes as $scope) {
            if (!in_array($scope, $tokenScopes)) {
                throw new MissingScopeException($scope);
            }
        }
    }
}
