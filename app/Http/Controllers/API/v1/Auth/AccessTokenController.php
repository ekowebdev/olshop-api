<?php

namespace App\Http\Controllers\API\v1\Auth;

use Request;
use Carbon\Carbon;
use App\Http\Models\User;
use Illuminate\Support\Facades\App;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use App\Http\Models\OauthAccessToken;
use App\Exceptions\DataEmptyException;
use App\Http\Models\OauthRefreshToken;
use App\Jobs\SendEmailVerificationJob;
use Nyholm\Psr7\Response as Psr7Response;
use App\Exceptions\AuthenticationException;
use Psr\Http\Message\ServerRequestInterface;
use Laravel\Passport\Http\Controllers\AccessTokenController as ApiAuthController;

class AccessTokenController extends ApiAuthController
{
    /**
     * [issueToken description]
     * @return [type] [description]
     */
    public function issueTokenRegister(ServerRequestInterface $serverRequest)
    {
        $locale = App::getLocale();
        $request = Request::all();

        User::validate($request, [
            'name' => 'required|string|max:255',
            'birthdate' => 'required|date',
            'username' => 'required|string|email:rfc,dns|unique:users,email|max:255',
            'password' => 'nullable|required_if:grant_type,password|string|min:6|confirmed|max:32',	        
	        'grant_type' =>	'required|in:password,social',
	        'provider' => 'nullable|required_if:grant_type,social|in:google',
			'google_id' => 'required_if:provider,google',
			'google_access_token' => 'required_if:provider,google',
        ]);

        \DB::beginTransaction();
        $username = strstr($request['username'], '@', true);
        if($request['grant_type'] == 'social'){
            $user = User::create([
                'username' => $username,
                'email' => $request['username'],
                'google_id' => $request['google_id'],
                'google_access_token' => $request['google_access_token'],
                'email_verified_at' => date('Y-m-d H:i:s')
            ]);
            $user->assignRole('customer');
            $user->profile()->create(['name' => $request['name'], 'birthdate' => $request['birthdate']]);
        } else {
            $user = User::create([
                'username' => $username,
                'email' => $request['username'],
                'password' => Hash::make($request['password'])
            ]);
            $user->assignRole('customer');
            $user->profile()->create(['name' => $request['name'], 'birthdate' => $request['birthdate']]);
            SendEmailVerificationJob::dispatch($locale, $user);
        }

        if(!empty($request['google_access_token'])){
            $request['access_token'] = $request['google_access_token'];
        }

        $request['is_register'] = true;

        $serverRequest = $serverRequest->withParsedBody($serverRequest->getParsedBody() + $request);
        
        request()->merge($request);

        $response = $this->issueToken($serverRequest);

        \DB::commit();

        return $response;
    }

    /**
     * [issueToken description]
     * @return [type] [description]
     */
    public function issueToken(ServerRequestInterface $serverRequest)
    {
        $locale = App::getLocale();
        $request = Request::all();

    	User::validate($request, [        
	        'username'	=> 'required|string|max:255',	        
	        'grant_type' =>	'required|in:password,social',
	        'password' => 'required_if:grant_type,password|string|min:6|max:32',
	        'provider' => 'nullable|required_if:grant_type,social|in:google',
			'access_token' => 'required_if:grant_type,social',
        ]);

        $parsedBody = array_merge($serverRequest->getParsedBody(), [
            'client_id' => env('OAUTH_CLIENT_ID'),
            'client_secret' => env('OAUTH_CLIENT_SECRET'),
        ]);

        $modifiedServerRequest = $serverRequest->withParsedBody($parsedBody);

        $user = User::where('email', $request['username'])->first();

        if(empty($user)){
            throw new AuthenticationException(trans('auth.account_not_registered'));
        }

        if($request['grant_type'] == 'social'){
            if($request['access_token'] != $user->google_access_token){
                throw new AuthenticationException(trans('auth.failed'));
            }
        }
        
        if($request['grant_type'] == 'password'){
            if($user->password == null) {
                throw new AuthenticationException(trans('auth.password_not_been_set'));
            }

            if (empty($user) OR !Hash::check($request['password'], $user->password, [])) {
                throw new AuthenticationException(trans('auth.failed'));
            }
        }

        $response = $this->withErrorHandling(function () use ($modifiedServerRequest) {
            return $this->convertResponse(
                $this->server->respondToAccessTokenRequest($modifiedServerRequest, new Psr7Response)
            );
        });

        if(!isJson($response->getContent())){
            throw new AuthenticationException($response->getContent());
        }
        
        $data = json_decode($response->getContent(), true);

        if(!empty($request['is_register'])){
            if($request['grant_type'] == 'password') {
                $message = trans('all.success_register');
            } else {
                $message = trans('all.success_register_without_verification');
            }
        } else {
            $message = trans('all.success_login');
        }

        return response()->json([
            'message' => $message,
            'data' => [
                'users' => new UserResource($user),
                'token_type' => 'Bearer',
                'expires_in' => $data['expires_in'],
                'access_token' => $data['access_token'],
                'refresh_token' => $data['refresh_token'],
            ],
            'status' => 200,
            'error' => 0
        ]);
    }

    /**
     * system login function
     *
     * @param ServerRequestInterface $serverRequest
     * @return boolean
     */
    public function issueTokenSystem(ServerRequestInterface $serverRequest)
    {
        $locale = App::getLocale();
        $request = Request::all();

        User::validate($request, [	        
	        'grant_type' => 'required|in:client_credentials,refresh_token',
            'refresh_token' => 'nullable|required_if:grant_type,refresh_token',
        ]);

        $parsedBody = array_merge($serverRequest->getParsedBody(), [
            'client_id' => env('OAUTH_CLIENT_ID'),
            'client_secret' => env('OAUTH_CLIENT_SECRET'),
        ]);

        $modifiedServerRequest = $serverRequest->withParsedBody($parsedBody);

        if($request['grant_type'] == 'refresh_token') {
            $appKey = env('APP_KEY');
            $encriptionKey = base64_decode(substr($appKey, 7));

            try {
                $crypto = \Defuse\Crypto\Crypto::decryptWithPassword($request['refresh_token'], $encriptionKey);
            } catch (\Exception $e){
                throw new AuthenticationException($e->getMessage());  
            }

            $crypto = json_decode($crypto, true);

            $accessToken = OauthAccessToken::where('id', $crypto['access_token_id'])
                ->where('revoked', 0)
                ->where('expires_at', '<' , Carbon::now())                    
                ->first();

            $refreshToken = OauthRefreshToken::where('id', $crypto['refresh_token_id'])
                ->where('access_token_id', $crypto['access_token_id'])    
                ->where('revoked', 0)
                ->where('expires_at', '>', Carbon::now())                    
                ->first();

            if(empty($refreshToken) || empty($accessToken)){
                throw new AuthenticationException(trans('error.failed_refresh_token'));  
            }

            $user = User::where('id', $accessToken['user_id'])->first();

            if (empty($user)) throw new DataEmptyException(trans('validation.attributes.data_not_exist', $locale));

            $response = $this->withErrorHandling(function () use ($modifiedServerRequest) {
                return $this->convertResponse(
                    $this->server->respondToAccessTokenRequest($modifiedServerRequest, new Psr7Response)
                );
            });

            if(!isJson($response->getContent())){
                throw new AuthenticationException($response->getContent());
            }
            
            $data = json_decode($response->getContent(), true);

            return response()->json([
                'message' => trans('all.success_refresh_token'),
                'data' => [
                    'users' => new UserResource($user),
                    'token_type' => 'Bearer',
                    'expires_in' => $data['expires_in'],
                    'access_token' => $data['access_token'],
                    'refresh_token' => $data['refresh_token'],
                ],
                'status' => 200,
                'error' => 0
            ]);
        } else {
            return $this->withErrorHandling(function () use ($modifiedServerRequest) {
                return $this->convertResponse(
                    $this->server->respondToAccessTokenRequest($modifiedServerRequest, new Psr7Response)
                );
            });
        }
    }
}