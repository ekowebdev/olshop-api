<?php

namespace App\Http\Traits;

use DateTimeImmutable;
use GuzzleHttp\Psr7\Response;
use Laravel\Passport\Passport;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Facades\DB;
use League\OAuth2\Server\CryptKey;
use Laravel\Passport\Bridge\Client;
use Laravel\Passport\TokenRepository;
use Laravel\Passport\Bridge\AccessToken;
use Laravel\Passport\Bridge\AccessTokenRepository;
use Laravel\Passport\Bridge\RefreshTokenRepository;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\ResponseTypes\BearerTokenResponse;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Exception\UniqueTokenIdentifierConstraintViolationException;

# https://github.com/laravel/passport/issues/71

/**
 * Trait PassportToken
 *
 * @package App\Traits
 */
trait PassportToken
{
    /**
     * Generate a new unique identifier.
     *
     * @param int $length
     *
     * @throws OAuthServerException
     *
     * @return string
     */
	private function generateUniqueIdentifier($length = 40)
	{
		try {
			return bin2hex(random_bytes($length));
		} catch (\TypeError $e) {
			throw OAuthServerException::serverError('An unexpected error has occurred');
		} catch (\Error $e) {
			throw OAuthServerException::serverError('An unexpected error has occurred');
		} catch (\Exception $e) {
			// If you get this message, the CSPRNG failed hard.
			throw OAuthServerException::serverError('Could not generate a random string');
		}
	}

	private function issueRefreshToken(AccessTokenEntityInterface $accessToken)
	{
		$maxGenerationAttempts = 10;
		$refreshTokenRepository = app(RefreshTokenRepository::class);

		$refreshToken = $refreshTokenRepository->getNewRefreshToken();
		$refreshToken->setExpiryDateTime((new DateTimeImmutable())->add(Passport::refreshTokensExpireIn()));
		$refreshToken->setAccessToken($accessToken);

		while ($maxGenerationAttempts-- > 0) {
			$refreshToken->setIdentifier($this->generateUniqueIdentifier());
			try {
				$refreshTokenRepository->persistNewRefreshToken($refreshToken);

				return $refreshToken;
			} catch (UniqueTokenIdentifierConstraintViolationException $e) {
				if ($maxGenerationAttempts === 0) {
					throw $e;
				}
			}
		}
	}
    
	private function createPassportTokenByUser($user, $clientId, $tokenScopes = [], $expirySecond = null)
	{
        $scopes = [];
        if (is_array($tokenScopes)) {
            foreach ($tokenScopes as $scope) {
                $scopes[] = new Scope($scope);
            }
        }

		$accessToken = new AccessToken($user->id ?? null, $scopes, new Client(null, null, null));
		$accessToken->setIdentifier($this->generateUniqueIdentifier());
		$accessToken->setClient(new Client($clientId, null, null));

        $expiry = (new DateTimeImmutable())->add(Passport::tokensExpireIn());
        if(!empty($expirySecond)){
            $expiry = (new DateTimeImmutable())->add(new \DateInterval('PT' . $expirySecond . 'S'));
        }

		$accessToken->setExpiryDateTime($expiry);
		$accessTokenRepository = new AccessTokenRepository(new TokenRepository(), new Dispatcher());
		$accessTokenRepository->persistNewAccessToken($accessToken);
		$refreshToken = $this->issueRefreshToken($accessToken);

		return [
			'access_token' => $accessToken,
			'refresh_token' => $refreshToken,
		];
	}

	private function sendBearerTokenResponse($accessToken, $refreshToken)
	{
	    //set private key
        $privateKey = new CryptKey('file://' . Passport::keyPath('oauth-private.key'), null, false);
        $accessToken->setPrivateKey($privateKey);

        $response = new BearerTokenResponse();
		$response->setAccessToken($accessToken);
		$response->setRefreshToken($refreshToken);

		//not used on laravel 6.x
		//$response->setPrivateKey($privateKey);

		$response->setEncryptionKey(app('encrypter')->getKey());

		return $response->generateHttpResponse(new Response);
	}

    /**
     * @param $clientId
     * @param bool $output default = true
     * @return array | \League\OAuth2\Server\ResponseTypes\BearerTokenResponse
     */
    protected function getBearerTokenByUser($user = null, $clientId, $output = true, $scopes = [], $expirySecond = null)
    {
        $passportToken = $this->createPassportTokenByUser($user, $clientId, $scopes, $expirySecond);
        $bearerToken = $this->sendBearerTokenResponse($passportToken['access_token'], $passportToken['refresh_token']);

        if(!empty($scopes)){
            DB::table('oauth_access_tokens')
            ->where('id', $passportToken['access_token']->getIdentifier())        
            ->update(['scopes'=> json_encode($scopes)]);
        }

        if (!$output) {
            $bearerToken = json_decode($bearerToken->getBody()->__toString(), true);
        }

        return $bearerToken;
    }

}