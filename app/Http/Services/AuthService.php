<?php
namespace App\Http\Services;

use Request;
use App\Http\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use App\Jobs\SendEmailVerificationJob;
use Illuminate\Support\Facades\Password;
use Laravel\Socialite\Facades\Socialite;
use App\Http\Repositories\UserRepository;
use App\Exceptions\AuthenticationException;
use App\Jobs\SendEmailTokenResetPasswordJob;

class AuthService extends BaseService
{
    private $model, $repository;

    public function __construct(User $model, UserRepository $repository)
    {
        $this->model = $model;
        $this->repository = $repository;
    }

    public function logout($locale)
	{
        if (auth()->check()) {
            auth()->user()->get_access_token()->delete();
            return response()->json([
                'message' => trans('all.success_logout'), 
                'status_code' => 200,
                'error' => 0,
            ], 200);
        }

        return response()->json([
            'error' => [
                'message' => trans('error.failed_logout'),
                'status_code' => 500,
                'error' => 1
            ]
        ], 500);
    }

    public function verify($id, $request)
    {
        if(!$request->hasValidSignature()){
            return response()->json([
                'error' => [
                    'message' => 'Failed verification email',
                    'status_code' => 500,
                    'error' => 1
                ]
            ], 500);
        }

        $user = $this->model->find($id);

        if(!$user->hasVerifiedEmail()){
            $user->markEmailAsVerified();
        }

        $url = config('setting.frontend.url') . '/email-verification-success';

        return redirect()->to($url);
    }

    public function resend($locale, $request)
    {
        $this->validate($request, [
            'email' => 'required|string|email:rfc,dns|exists:users,email',
        ]);

        $user = $this->repository->getDataByMultipleParam(['email' => $request['email']]);

        if($user->email_verified_at != null){
            return response()->json([
                'error' => [
                    'message' => trans('error.already_verification'),
                    'status_code' => 400,
                    'error' => 1
                ]
            ], 400);
        }

        SendEmailVerificationJob::dispatch($user, $locale);

        return response()->json([
            'message' => trans('all.success_resend_verification'), 
            'status_code' => 200,
            'error' => 0,
        ], 200);
    }

    public function forget_password($locale, $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        DB::beginTransaction();

        DB::table('password_resets')->where('email', $request->email)->delete();

        $token = Str::random(16);

        $data['token'] = Hash::make($token);
        $data['email'] = $request->email;
        $data['created_at'] = date('Y-m-d H:i:s');

        DB::table('password_resets')->insert($data);

        SendEmailTokenResetPasswordJob::dispatch($request->email, $data, $locale);

        DB::commit();

        return response()->json([
            'message' => trans('all.success_send_reset_password_link'), 
            'status_code' => 200,
            'error' => 0,
        ], 200);
    }

    public function reset_password($locale, $request) 
    {
        $request->validate([
            'token' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        $password_reset = DB::table('password_resets')->where('token', $request->token)->first();

        if($password_reset == null){
            return response()->json([
                'error' => [
                    'message' => trans('error.token_reset_password_is_invalid'),
                    'status_code' => 400,
                    'error' => 1
                ]
            ], 400);
        }

        if (strtotime($password_reset->created_at) < strtotime('-60 minutes')) {
            DB::table('password_resets')->where('token', $request->token)->delete();
            return response()->json([
                'error' => [
                    'message' => trans('error.token_reset_password_is_expired'),
                    'status_code' => 401,
                    'error' => 1
                ]
            ], 401);
        }

        DB::beginTransaction();

        $user = $this->model->where('email', $password_reset->email)->first();

        $user->update(['password' => Hash::make($request->password)]);

        DB::table('password_resets')->where('email', $password_reset->email)->delete();

        DB::commit();

        return response()->json([
            'message' => trans('all.success_reset_password'), 
            'status_code' => 200,
            'error' => 0,
        ], 200);
    }

    public function auth_google($locale)
    {
        return response()->json([
            'data' => [
                'auth_url' => Socialite::driver('google')->stateless()->redirect()->getTargetUrl(),
            ],
            'status_code' => 200,
            'error' => 0,
        ], 200);
    }

    public function auth_google_callback($locale)
    {
        try {
            $socialite = Socialite::driver('google')->stateless()->user();
            $url = config('setting.frontend.url') . '/auth-success?email='.$socialite->email.'&google_access_token='.$socialite->token;
            return redirect()->to($url);
        } catch (\Exception $e) {
            $url = config('setting.frontend.url') . '/login?error='.$e->getMessage();
            return redirect()->to($url);
        }
    }
}
