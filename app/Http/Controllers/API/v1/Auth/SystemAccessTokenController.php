<?php

namespace App\Http\Controllers\API\v1\Auth;

use Request;
use App\Http\Controllers\Controller;
use Nyholm\Psr7\Response as Psr7Response;
use Psr\Http\Message\ServerRequestInterface;
use Laravel\Passport\Http\Controllers\AccessTokenController;

class SystemAccessTokenController extends AccessTokenController
{
    public function tokenSystem(ServerRequestInterface $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make(Request::all(),[
            'client_id'     => 'required',
            'client_secret'	=> 'required',	        
            'grant_type'	=> 'required|in:client_credentials',        
        ]);
          
        if($validator->fails()){ 
            throw new \App\Exceptions\ValidationException($validator->errors());
        }
        
        return $this->withErrorHandling(function () use ($request) {
            return $this->convertResponse(
                $this->server->respondToAccessTokenRequest($request, new Psr7Response)
            );
        });
    }
}
