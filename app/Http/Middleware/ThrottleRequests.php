<?php

namespace App\Http\Middleware;

use Closure;
use RuntimeException;
use Illuminate\Support\Str;
use Illuminate\Cache\RateLimiter;
use Illuminate\Support\InteractsWithTime;
use Symfony\Component\HttpFoundation\Response;

class ThrottleRequests
{
    use InteractsWithTime;

    protected $custom_message;

    /**
     * The rate limiter instance.
     *
     * @var \Illuminate\Cache\RateLimiter
     */
    protected $limiter;

    /**
     * Create a new request throttler.
     *
     * @param  \Illuminate\Cache\RateLimiter $limiter
     */
    public function __construct(RateLimiter $limiter, $custom_message='')
    {
        $this->limiter = $limiter;
        $this->custom_message = $custom_message;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @param  int $maxAttempts
     * @param  int $decayMinutes
     * @return mixed
     */
    public function handle($request, Closure $next, $maxAttempts = 60, $decayMinutes = 1)
    {
        \App::setLocale(\Route::current()->parameter('locale'));

        if(empty(\App::getLocale())){
            \App::setLocale('id');
        }
        
        $key = $this->resolveRequestSignature($request);

        $maxAttempts = $this->resolveMaxAttempts($request, $maxAttempts);

        if ($this->limiter->tooManyAttempts($key, $maxAttempts, $decayMinutes)) {
            return $this->buildResponse($key, $maxAttempts);
        }

        $this->limiter->hit($key, $decayMinutes);

        $response = $next($request);

        return $this->addHeaders(
            $response, $maxAttempts,
            $this->calculateRemainingAttempts($key, $maxAttempts)
        );
    }

    /**
     * Resolve request signature.
     *
     * @param  \Illuminate\Http\Request $request
     * @return string
     */
    protected function resolveRequestSignature($request)
    {
        if( $bearerToken = $request->bearerToken() ) {
            $tokenId    = (new \Lcobucci\JWT\Parser())->parse($bearerToken)->getHeader('jti');
            return sha1(url()->current().'|'.$tokenId);
        }
        
        if ($user = $request->user()) {
            return sha1($user->getAuthIdentifier());
        }

        if ($route = $request->route()) {
            #exp : "otpresend"
            // if(!empty($route->getActionMethod())){
            //     return sha1($route->getDomain().'|'.$route->getActionMethod().'|'.$request->ip());
            // }

            #exp : "\App\Http\Controllers\Api\v1\Admin\CustomerAuthenticationController@otpresend"
            if(!empty($route->getActionName())){
                if(!empty($request->header('User-Agent'))){
                    return sha1($route->getDomain().'|'.$route->getActionName().'|'.$request->ip().'|'.$request->header('User-Agent'));
                }
                return sha1($route->getDomain().'|'.$route->getActionName().'|'.$request->ip());
            }

            return sha1($route->getDomain().'|'.$request->ip());
        }

        throw new RuntimeException('Unable to generate the request signature. Route unavailable.');
    }

    protected function resolveMaxAttempts($request, $maxAttempts)
    {
        if (Str::contains($maxAttempts, '|')) {
            $maxAttempts = explode('|', $maxAttempts, 2)[$request->user() ? 1 : 0];
        }

        if (! is_numeric($maxAttempts) && $request->user()) {
            $maxAttempts = $request->user()->{$maxAttempts};
        }

        return (int) $maxAttempts;
    }

    /**
     * Create a 'too many attempts' response.
     *
     * @param  string $key
     * @param  int $maxAttempts
     * @return \Illuminate\Http\Response
     */
    protected function buildResponse($key, $maxAttempts)
    {
        $retryAfter = $this->limiter->availableIn($key);

        // $AfterDate = \Carbon\Carbon::now()->addSeconds($retryAfter);
        $error_message = $this->custom_message;
        if(empty($custom_message)){
            $error_message = trans('validation.too_many_requests_wait',[
                'wait' => $this->humanTime($retryAfter)
            ]);
        }
        
        $message = json_encode([
            'error' => [
                'message' => $error_message, 
                'status_code' => 429,
                'error' => $error_message,
            ]],429);

        $response = new Response($message, 429);

        return $this->addHeaders(
            $response, $maxAttempts,
            $this->calculateRemainingAttempts($key, $maxAttempts, $retryAfter),
            $retryAfter
        );
    }

    /**
     * Add the limit header information to the given response.
     *
     * @param  \Symfony\Component\HttpFoundation\Response $response
     * @param  int $maxAttempts
     * @param  int $remainingAttempts
     * @param  int|null $retryAfter
     * @return \Illuminate\Http\Response
     */
    protected function addHeaders(Response $response, $maxAttempts, $remainingAttempts, $retryAfter = null)
    {
        $headers = [
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => $remainingAttempts,
        ];

        if (!is_null($retryAfter)) {
            $headers['X-RateLimit-Reset'] = $this->availableAt($retryAfter);
            $headers['Retry-After'] = $retryAfter;
            $headers['Content-Type'] = 'application/json';
        }

        $response->headers->add($headers);

        return $response;
    }

    /**
     * Calculate the number of remaining attempts.
     *
     * @param  string $key
     * @param  int $maxAttempts
     * @param  int|null $retryAfter
     * @return int
     */
    protected function calculateRemainingAttempts($key, $maxAttempts, $retryAfter = null)
    {
        if (!is_null($retryAfter)) {
            return 0;
        }

        return $this->limiter->retriesLeft($key, $maxAttempts);
    }

    protected function humanTime($secs, $short = true){
        $response = $secs;
        if($secs > 0){
            $humansFormat = \Carbon\CarbonInterval::seconds($secs);
            $response = $humansFormat->cascade()->forHumans();
            if($short){
                $response = $humansFormat->cascade()->forHumans(['short' => true]); // 4h 30m
            }
        }
        
        return $response;
    }
    
}
