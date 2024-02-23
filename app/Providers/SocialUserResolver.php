<?php
namespace App\Providers;

use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use GuzzleHttp\Client;
use App\Http\Models\User;
use App\Exceptions\ApplicationException;
use GuzzleHttp\Exception\GuzzleException;
use Adaojunior\Passport\SocialGrantException;
use Adaojunior\Passport\SocialUserResolverInterface;

class SocialUserResolver implements SocialUserResolverInterface
{

    /**
     * Resolves user by given network and access token.
     *
     * @param string $network
     * @param string $accessToken
     * @return \Illuminate\Contracts\Auth\Authenticatable
     */
    public function resolve($network, $accessToken, $accessTokenSecret = null)
    {
        switch ($network) {
            case 'google':
                return $this->authWithGoogle($accessToken);
                break;
            default:
                throw SocialGrantException::invalidNetwork();
                break;
        }
    }

    /**
     * Resolves user by google access token.
     *
     * @param string $accessToken
     * @return User
     */
    public function authWithGoogle($accessToken)
    {
        $client = new Client(); //GuzzleHttp\Client
        $response = $client->request('GET', 'https://www.googleapis.com/oauth2/v1/tokeninfo?id_token='.$accessToken, ['http_errors' => false]);        
        
        if( $response->getStatusCode() != 200 ) {
            return false;
        }

        $data = User::where('google_id', '=', json_decode($response->getBody())->user_id)->first();
        
        if($data === null) {
            return false;
        }
        
        $data->update(['google_access_token' => $accessToken]);
        
        return $data;
    }

}