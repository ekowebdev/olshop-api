<?php

namespace App\Http\Controllers\API\v1\Auth;

use Request;
use Carbon\Carbon;
use GuzzleHttp\Client;
use App\Rules\ReCaptcha;
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
            'birthdate' => 'nullable|required_if:grant_type,password|date',
            'username' => 'required|string|email:rfc,dns|unique:users,email|max:255',
            'password' => 'nullable|required_if:grant_type,password|string|min:6|confirmed|max:32',
	        'grant_type' =>	'required|in:password,social',
	        'provider' => 'nullable|required_if:grant_type,social|in:google',
			'access_token' => 'nullable|required_if:grant_type,social',
            // 'g-recaptcha-response' => ['nullable', 'required_if:grant_type,password', new ReCaptcha],
            'g-recaptcha-response' => [
                (!empty($request['g-recaptcha-response']) && $request['g-recaptcha-response'] != config('services.recaptcha.development_key')) ? new ReCaptcha : '',
                'required_if:grant_type,password',
            ],
        ]);

        \DB::beginTransaction();
        $username = strstr($request['username'], '@', true);
        if($request['grant_type'] == 'social'){
            $user = User::create([
                'username' => $username,
                'email' => $request['username'],
                'email_verified_at' => date('Y-m-d H:i:s')
            ]);
            $user->assignRole('customer');
            $user->profile()->create(['name' => $request['name']]);
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
            'client_id' => 'required|exists:oauth_clients,id',
            'client_secret' => 'required|exists:oauth_clients,secret',
	        'username'	=> 'required|string|max:255',
	        'grant_type' =>	'required|in:password,social',
	        'password' => 'nullable|required_if:grant_type,password|string|min:6|max:32',
	        'provider' => 'nullable|required_if:grant_type,social|in:google',
			'access_token' => 'nullable|required_if:grant_type,social',
            'g-recaptcha-response' => [
                (!empty($request['g-recaptcha-response']) && $request['g-recaptcha-response'] != config('services.recaptcha.development_key')) ? new ReCaptcha : '',
                'required_if:grant_type,password',
            ],
        ]);

        try {
            \DB::beginTransaction();

            $user = User::where('email', $request['username'])->first();

            if(empty($user)) throw new DataEmptyException(trans('validation.attributes.data_not_exist', ['attr' => 'User'], $locale));

            if($request['grant_type'] == 'password'){
                if($user->password === null) throw new AuthenticationException();
                if (!Hash::check($request['password'], $user->password, [])) throw new AuthenticationException();
            }

            if($request['grant_type'] == 'social'){
                $checkGoogleCredentials = $this->checkGoogleCredentials($request['access_token']);
                if($checkGoogleCredentials === false) throw new AuthenticationException();
            }

            $response = $this->withErrorHandling(function () use ($serverRequest) {
                return $this->convertResponse(
                    $this->server->respondToAccessTokenRequest($serverRequest, new Psr7Response)
                );
            });

            $data = json_decode($response->getContent(), true);

            if(!empty($request['is_register'])){
                if($request['grant_type'] == 'password') {
                    $message = trans('all.success_register_with_verification');
                } else {
                    $message = trans('all.success_register');
                }
            } else {
                $message = trans('all.success_login');
            }

            \DB::commit();

            $responseData = [
                'users' => new UserResource($user),
                'token_type' => 'Bearer',
                'expires_in' => $data['expires_in'],
                'access_token' => $data['access_token'],
                'refresh_token' => $data['refresh_token'],
            ];
            return response()->api($message, $responseData);
        } catch (\Exception $e){
            \DB::rollback();
            throw new AuthenticationException($e->getMessage());
        }
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
            'client_id' => 'required|exists:oauth_clients,id',
            'client_secret' => 'required|exists:oauth_clients,secret',
	        'grant_type' => 'required|in:client_credentials,refresh_token',
            'refresh_token' => 'nullable|required_if:grant_type,refresh_token',
        ]);

        if($request['grant_type'] == 'refresh_token') {
            $appKey = config('app.key');
            $encriptionKey = base64_decode(substr($appKey, 7));

            try {
                $crypto = \Defuse\Crypto\Crypto::decryptWithPassword($request['refresh_token'], $encriptionKey);
            } catch (\Exception $e){
                throw new AuthenticationException(trans('error.refresh_token_is_invalid', ['error' => $e->getMessage()]));
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

            if(empty($refreshToken) || empty($accessToken)) throw new AuthenticationException(trans('error.failed_refresh_token'));

            $user = User::where('id', $accessToken['user_id'])->first();

            if(empty($user)) throw new DataEmptyException(trans('validation.attributes.data_not_exist', ['attr' => 'User'], $locale));

            $response = $this->withErrorHandling(function () use ($serverRequest) {
                return $this->convertResponse(
                    $this->server->respondToAccessTokenRequest($serverRequest, new Psr7Response)
                );
            });

            if(!isJson($response->getContent())) throw new AuthenticationException($response->getContent());

            $data = json_decode($response->getContent(), true);

            $responseData = [
                'users' => new UserResource($user),
                'token_type' => 'Bearer',
                'expires_in' => $data['expires_in'],
                'access_token' => $data['access_token'],
                'refresh_token' => $data['refresh_token'],
            ];
            return response()->api(trans('all.success_refresh_token'), $responseData);
        } else {
            return $this->withErrorHandling(function () use ($serverRequest) {
                return $this->convertResponse(
                    $this->server->respondToAccessTokenRequest($serverRequest, new Psr7Response)
                );
            });
        }
    }

    private function checkGoogleCredentials($accessToken)
    {
        $client = new Client();
        $response = $client->request('GET', 'https://www.googleapis.com/oauth2/v2/tokeninfo?accessToken='.$accessToken, ['http_errors' => false]);
        if($response->getStatusCode() != 200) return false;

        $data = User::where('email', '=', json_decode($response->getBody())->email)->first();
        if($data === null) return false;

        \DB::beginTransaction();
        if($data->google_id === null) $data->update(['google_id' => json_decode($response->getBody())->user_id]);
        $data->update(['google_access_token' => $accessToken]);
        \DB::commit();

        return $data;
    }
}
