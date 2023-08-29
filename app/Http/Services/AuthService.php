<?php
namespace App\Http\Services;

use App\Http\Models\User;
use App\Http\Services\BaseService;
use App\Http\Traits\PassportToken;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Jobs\SendEmailVerificationJob;
use Illuminate\Auth\Events\Registered;
use App\Http\Repositories\UserRepository;
use App\Http\Repositories\OauthRepository;
use App\Exceptions\AuthenticationException;
use Illuminate\Support\Facades\Notification;
use App\Notifications\VerifyEmailNotification;

class AuthService extends BaseService
{  
    use PassportToken;

    private $model, $repository, $oauth_repository, $oauth_client_id;

    public function __construct(User $model, UserRepository $repository, OauthRepository $oauth_repository)
    {
        $this->model = $model;
        $this->repository = $repository;
        $this->oauth_repository = $oauth_repository;
        $this->oauth_client_id = env('OAUTH_CLIENT_ID');
    }

    public function register($locale, $request)
    {
        $this->validate($request, [
            'name' => 'required|string|max:255',
            'username' => 'required|string|unique:users|max:255',
            'email' => 'required|string|email|unique:users|max:255',
            'password' => 'required|string|min:6|confirmed',
        ]);

        DB::beginTransaction();
        $user = $this->model->create([
            'name' => $request['name'],
            'username' => $request['username'],
            'email' => $request['email'],
            'password' => Hash::make($request['password']),
        ]);
        $user->assignRole($request['role']);
        $user->sendEmailVerificationNotification();
        DB::commit();

        $data = [
            'message' => trans('all.success_register'),
            'status' => 200,
            'error' => 0,
        ];

        return response()->json($data);
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
                'users' => new UserResource($user),
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

        $check_refresh_token = $this->oauth_repository->checkRefreshToken($crypto['refresh_token_id'], $crypto['access_token_id']);
        if(empty($check_refresh_token)){
            throw new AuthenticationException(trans('error.failed_refresh_token'));
        }
        $check_access_token = $this->oauth_repository->checkAccessToken($crypto['access_token_id']);

        if(empty($check_access_token)){
            throw new AuthenticationException(trans('error.failed_refresh_token'));  
        }

        $user = $this->repository->getSingleData($locale, $check_access_token['user_id']);

        if (empty($user)) {
            throw new AuthenticationException(trans('error.failed_refresh_token'));            
        }

        $check_refresh_token->update(['revoked' => 1]);
        $check_access_token->update(['revoked' => 1]);
                
        $token_response = $this->getBearerTokenByUser($user, $this->oauth_client_id, false);
        
        $data = [
            'message' => trans('all.success_refresh_token'),
            'data' => [
                'users' => new UserResource($user),
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

    public function verify($id, $request)
    {
        if(!$request->hasValidSignature()){
            return response()->json([
                'message' => 'Verifikasi email gagal.', 
                'status' => 400,
                'error' => 0,
            ]);
        }

        $user = User::find($id);

        if(!$user->hasVerifiedEmail()){
            $user->markEmailAsVerified();
        }

        return redirect()->to('/');
    }

    public function notice()
    {
        return response()->json([
            'message' => 'Anda belum melakukan verifikasi email.', 
            'status' => 400,
            'error' => 0,
        ]);
    }

    public function resend($locale)
    {
        if(auth()->user()->hasVerifiedEmail()){
            return response()->json([
                'message' => trans('error.already_verification'), 
                'status' => 200,
                'error' => 0,
            ]);
        }

        auth()->user()->sendEmailVerificationNotification();
        // dispatch(new SendEmailVerificationJob(auth()->user()));

        return response()->json([
            'message' => trans('all.success_resend_verification'), 
            'status' => 200,
            'error' => 0,
        ]);
    }
}
