<?php

namespace App\Http\Controllers\API\v1\Auth;

use Illuminate\Http\Request;
use App\Http\Services\AuthService;
use App\Http\Controllers\BaseController;

class AuthController extends BaseController
{
    private $service;

    public function __construct(AuthService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    public function logout($locale)
	{
        return $this->service->logout($locale);
    }

    public function verify($id, Request $request)
    {
        return $this->service->verify($id, $request);
    }

    public function resend($locale, Request $request)
    {   
        return $this->service->resend($locale, $request->all());
    }

    public function forget_password($locale, Request $request)
    {
        return $this->service->forget_password($locale, $request);
    }

    public function reset_password($locale, Request $request)
    {
        return $this->service->reset_password($locale, $request);
    }

    public function auth_google($locale)
    {
        return $this->service->auth_google($locale);
    }

    public function auth_google_callback($locale)
    {
        return $this->service->auth_google_callback($locale);
    }
}