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

    public function register($locale, Request $request)
    {   
        return $this->service->register($locale, $request->all());
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

    public function verify($id, Request $request)
    {
        return $this->service->verify($id, $request);
    }

    // public function notice()
    // {   
    //     return $this->service->notice();
    // }

    public function resend($locale)
    {   
        return $this->service->resend($locale);
    }
}