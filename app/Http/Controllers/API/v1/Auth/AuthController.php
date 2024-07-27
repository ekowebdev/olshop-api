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

    public function forgetPassword($locale, Request $request)
    {
        return $this->service->forgetPassword($locale, $request);
    }

    public function resetPassword($locale, Request $request)
    {
        return $this->service->resetPassword($locale, $request);
    }

    public function authGoogle($locale)
    {
        return $this->service->authGoogle($locale);
    }

    public function authGoogleCallback($locale)
    {
        return $this->service->authGoogleCallback($locale);
    }
}
