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
use App\Exceptions\ConflictException;
use App\Exceptions\ApplicationException;
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
            auth()->user()->getAccessToken()->delete();
            return response()->api(trans('all.success_logout'));
        }

        throw new ApplicationException(trans('error.failed_logout'));
    }

    public function verify($id, $request)
    {
        if(!$request->hasValidSignature()) throw new ApplicationException(trans('error.failed_verification_email'));

        $user = $this->model->find($id);

        if(!$user->hasVerifiedEmail()) $user->markEmailAsVerified();

        $url = config('setting.frontend.url') . '/email-verification-success';

        return redirect()->to($url);
    }

    public function resend($locale, $request)
    {
        $this->validate($request, [
            'email' => 'required|string|email:rfc,dns|exists:users,email',
        ]);

        $user = $this->repository->getDataByMultipleParam(['email' => $request['email']]);

        if($user->email_verified_at != null) throw new ConflictException(trans('error.already_verification'));

        SendEmailVerificationJob::dispatch($locale, $user);

        return response()->api(trans('all.success_resend_verification'));

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

        return response()->api(trans('all.success_send_reset_password_link'));
    }

    public function reset_password($locale, $request)
    {
        $request->validate([
            'token' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        DB::beginTransaction();

        $passwordReset = DB::table('password_resets')->where('token', $request->token)->first();

        if($passwordReset == null) throw new ApplicationException(trans('error.token_reset_password_is_invalid'));

        if (strtotime($passwordReset->created_at) < strtotime('-60 minutes')) {
            DB::table('password_resets')->where('token', $request->token)->delete();
            throw new ApplicationException(trans('error.token_reset_password_is_expired'));
        }

        $user = $this->model->where('email', $passwordReset->email)->first();

        $user->update(['password' => Hash::make($request->password)]);

        DB::table('password_resets')->where('email', $passwordReset->email)->delete();

        DB::commit();

        return response()->api(trans('all.success_reset_password'));
    }

    public function authGoogle($locale)
    {
        $data = [
            'auth_url' => Socialite::driver('google')->stateless()->redirect()->getTargetUrl(),
        ];

        return response()->api(trans('all.success_logout'), $data);
    }

    public function authGoogleCallback($locale)
    {
        try {
            $socialite = Socialite::driver('google')->stateless()->user();
            $user = $this->model->where('email', $socialite->email)->first();

            if(empty($user)) $url = config('setting.frontend.url') . '/auth-success?is_register=true&name='.$socialite->user['given_name'].'&email='.$socialite->email.'&google_access_token='.$socialite->token;
            else $url = config('setting.frontend.url') . '/auth-success?email='.$socialite->email.'&google_access_token='.$socialite->token;

            return redirect()->to($url);
        } catch (\Exception $e) {
            $url = config('setting.frontend.url') . '/login?error='.$e->getMessage();
            return redirect()->to($url);
        }
    }
}
