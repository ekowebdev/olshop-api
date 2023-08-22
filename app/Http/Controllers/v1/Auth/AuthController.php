<?php

namespace App\Http\Controllers\v1\Auth;

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

    public function login($locale, Request $request)
    {   
        return $this->service->login($locale, $request->all());
    }

    public function refresh_token($locale, Request $request)
    {   
        return $this->service->refresh_token($locale, $request->all());
    }

    public function logout($locale)
	{
        return $this->service->logout($locale);
    }
}