<?php

namespace App\Exceptions;

use Throwable;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        ApplicationException::class,
        AuthenticationException::class,
        ForbiddenException::class,
        DataEmptyException::class,
        ValidationException::class,
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Throwable $exception)
    {
        if (method_exists($exception, 'responseJson')) {
            return $exception->responseJson();
        }

        if ($request->ajax() || $request->wantsJson()) {
            // dd(method_exists($exception, 'getStatusCode'));
            $exception = $this->prepareException($exception);

            if ($exception instanceof \Illuminate\Http\Exception\HttpResponseException) {
                return $exception->getResponse();
            }

            if ($exception instanceof \Illuminate\Auth\AuthenticationException) {
                return $this->unauthenticated($request, $exception);
            }

            if ($exception instanceof \Illuminate\Validation\ValidationException) {
                return $this->convertValidationExceptionToResponse($exception, $request);
            }

            if ($exception instanceof UnauthorizedException) {
                return response()->json([
                    'error' => [
                        'message' => trans('auth.not_authorize_access'),
                        'status_code' => 403,
                        'error' => 1
                    ]
                ]);
            }

            $statusCode = method_exists($exception, 'getStatusCode') ? $exception->getStatusCode() : 500;

            $response = [
                'error' => [
                    'message' => $exception->getMessage(),
                    'status_code' => $statusCode,
                    'error' => 1
                ]
            ];

            if(config('app.debug')) {
                $response['error']['message'] = $exception->getMessage();
                $response['error']['line'] = $exception->getLine();
                $response['error']['file'] = $exception->getFile();
                $response['error']['class'] = get_class($exception);
                $response['error']['trace'] = $exception->getTrace();
                $response['error']['code'] = $exception->getCode();
                $response['error']['status_code'] = $statusCode;
            }

            return response()->json($response, $statusCode);
        }

        return parent::render($request, $exception);
    }

    /**
     * [unauthenticated description]
     * @param  [type]                                   $request   [description]
     * @param  \Illuminate\Auth\AuthenticationException $exception [description]
     * @return [type]                                              [description]
     */
    protected function unauthenticated($request, \Illuminate\Auth\AuthenticationException $exception)
    {
        throw new AuthenticationException(trans('auth.failed'));
    }
}
