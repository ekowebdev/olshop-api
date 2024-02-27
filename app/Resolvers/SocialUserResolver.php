<?php

namespace App\Resolvers;

use App\Http\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Contracts\Auth\Authenticatable;
use Laravel\Socialite\Two\User as ProviderUser;
use Coderello\SocialGrant\Resolvers\SocialUserResolverInterface;

class SocialUserResolver implements SocialUserResolverInterface
{
    /**
     * Resolve user by provider credentials.
     */
    public function resolveUserByProviderCredentials(string $provider, string $accessToken): ?Authenticatable
    {
        // Return the user that corresponds to provided credentials.
        // If the credentials are invalid, then return NULL.
         $providerUser = Socialite::driver($provider)->userFromToken($accessToken);
         
         return $this->findOrCreateUser($provider, $providerUser);;
    }

    protected function findOrCreateUser(string $provider, ProviderUser $providerUser): ?Authenticatable
    {
        // todo your logic here      
        $id = $providerUser->getId();
        $email = $providerUser->getEmail();
        $user = User::where('email', $email)->where('google_id', $id)->first();
        return $user;
    }
}