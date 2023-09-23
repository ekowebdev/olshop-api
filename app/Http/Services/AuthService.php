<?php
namespace App\Http\Services;

use Carbon\Carbon;
use App\Http\Models\User;
use Illuminate\Support\Str;
use App\Http\Services\BaseService;
use App\Http\Traits\PassportToken;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Jobs\SendEmailVerificationJob;
use Illuminate\Auth\Events\Registered;
use App\Jobs\SendEmailResetPasswordLinkJob;
use Illuminate\Support\Facades\Password;
use App\Http\Repositories\UserRepository;
use Illuminate\Auth\Events\PasswordReset;
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
            'email' => 'required|string|email:rfc,dns|unique:users|max:255',
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
        // $user->sendEmailVerificationNotification();
        SendEmailVerificationJob::dispatch($user);
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
            'email' => 'required|string|email:rfc,dns|exists:users',
            'password' => 'required|string|min:6|max:12'
        ]);

        $user = $this->repository->getDataByMultipleParam(['email' => $request['email']]);

        if($user->email_verified_at == null){
            throw new AuthenticationException(trans('auth.not_verified_account'));
        }

        if(empty($user)){
            throw new AuthenticationException(trans('auth.wrong_email_or_password'));
        }

        if (empty($user) OR !Hash::check($request['password'], $user->password, [])) {
            throw new AuthenticationException(trans('auth.wrong_email_or_password'));
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
                'message' => 'Gagal verifikasi email', 
                // 'message' => trans('error.failed_verifcation_email'), 
                'status' => 400,
                'error' => 0,
            ]);
        }

        $user = User::find($id);

        if(!$user->hasVerifiedEmail()){
            $user->markEmailAsVerified();
        }

        $url = env('FRONT_URL') . '/email-verification-success';

        return redirect()->to($url);
    }

    public function resend($locale, $request)
    {
        $user = $this->repository->getDataByMultipleParam(['email' => $request->email]);
        
        if($user->email_verified_at != null){
            return response()->json([
                'message' => trans('error.already_verification'), 
                'status' => 200,
                'error' => 0,
            ]);
        }

        // auth()->user()->sendEmailVerificationNotification();
        SendEmailVerificationJob::dispatch($user);

        return response()->json([
            'message' => trans('all.success_resend_verification'), 
            'status' => 200,
            'error' => 0,
        ]);
    }

    public function forget_password($locale, $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users',
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        // SendEmailResetPasswordLinkJob::dispatch($request->email);

        if($status != Password::RESET_LINK_SENT) {
            return response()->json([
                'message' => trans('error.failed_send_reset_password_link'), 
                'status' => 200,
                'error' => 0,
            ]);
        }

        return response()->json([
            'message' => trans('all.success_send_reset_password_link'), 
            'status' => 200,
            'error' => 0,
        ]);
    }

    public function reset_password($token) 
    {
        $url = env('FRONT_URL') . '/reset-password?token=' . $token; 
        return redirect()->to($url);
    }

    public function reset_password_update($locale, $request) 
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email|exists:users',
            'password' => 'required|min:6|confirmed',
        ]);
     
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));
     
                $user->save();
     
                event(new PasswordReset($user));
            }
        );

        if($status !== Password::PASSWORD_RESET) {
            return response()->json([
                'message' => trans('error.failed_reset_password'), 
                'status' => 200,
                'error' => 0,
            ]);
        }

        return response()->json([
            'message' => trans('all.success_reset_password'), 
            'status' => 200,
            'error' => 0,
        ]);
    }
}
