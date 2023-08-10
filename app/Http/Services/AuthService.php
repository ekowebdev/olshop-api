<?php
namespace App\Http\Services;

use App\Http\Traits\PassportToken;
use Illuminate\Support\Facades\Hash;
use App\Http\Repositories\UserRepository;
use App\Http\Repositories\OauthRepository;
use App\Exceptions\AuthenticationException;

class AuthService extends BaseService
{  
    use PassportToken;

    private $repository, $oauth_repository, $oauth_client_id;

    public function __construct(UserRepository $repository, OauthRepository $oauth_repository)
    {
        $this->repository = $repository;
        $this->oauth_repository = $oauth_repository;
        $this->oauth_client_id = env('OAUTH_CLIENT_ID');
    }

    public function login($locale, $request)
    {
        $this->validate($request, [
            'username' => 'required',
            'password' => 'required|min:6|max:12|string'
        ]);

        $user = $this->repository->getDataByUsername($locale, $request['username']);

        if(empty($user)){
            throw new AuthenticationException(trans('auth.wrong_username_or_password'));
        }
        if (empty($user) OR !Hash::check($request['password'], $user->password, [])) {
            throw new AuthenticationException(trans('auth.wrong_username_or_password'));
        }
        
        $token_response = $this->getBearerTokenByUser($user, $this->oauth_client_id, false);
        
        $data = [
            'message' => trans('all.success_login'),
            'data' => [
                'user_id' => $user->id,
                'token_type' => 'Bearer',
                'expires_in' => $token_response['expires_in'],
                'access_token' => $token_response['access_token'],
                'refresh_token' => $token_response['refresh_token'],
            ],
            'status' => 200,
            'error' => 0
        ];

        return response()->json($data);
    }

    public function refresh_token($locale, $request)
    {
        $this->validate($request, [
            'refresh_token' => 'required|string',
        ]);

        $app_key = env('APP_KEY');
        $enc_key = base64_decode(substr($app_key, 7));

        try {
            $crypto = \Defuse\Crypto\Crypto::decryptWithPassword($request['refresh_token'], $enc_key);
        } catch (\Exception $e){
            throw new AuthenticationException(trans('error.failed_refresh_token'));  
        }
        $crypto = json_decode($crypto, true);

        $checkRefreshToken = $this->oauth_repository->checkRefreshToken($crypto['refresh_token_id'], $crypto['access_token_id']);
        if(empty($checkRefreshToken)){
            throw new AuthenticationException(trans('error.failed_refresh_token'));
        }
        $checkAccessToken = $this->oauth_repository->checkAccessToken($crypto['access_token_id']);

        if(empty($checkAccessToken)){
            throw new AuthenticationException(trans('error.failed_refresh_token'));  
        }

        $user = $this->repository->getSingleData($locale, $checkAccessToken['user_id']);

        if (empty($user)) {
            throw new AuthenticationException(trans('error.failed_refresh_token'));            
        }

        $checkRefreshToken->update(['revoked' => 1]);
        $checkAccessToken->update(['revoked' => 1]);
                
        $token_response = $this->getBearerTokenByUser($user, $this->oauth_client_id, false);
        
        $data = [
            'message' => trans('all.success_refresh_token'),
            'data' => [
                'user_id' => $user->id,
                'token_type' => 'Bearer',
                'expires_in' => $token_response['expires_in'],
                'access_token' => $token_response['access_token'],
                'refresh_token' => $token_response['refresh_token'],
            ],
            'status' => 200,
            'error' => 0
        ];

        return response()->json($data);
    }

    public function logout($locale)
	{
        auth()->user()->getAccessToken()->delete();
        return response()->json([
                'message' => trans('all.success_logout'), 
                'status' => 200,
                'error' => 0,
            ]);
    }
}
