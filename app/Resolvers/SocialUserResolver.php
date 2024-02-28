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
     * Return the user that corresponds to provided credentials.
     * If the credentials are invalid, then return NULL.
     */
    public function resolveUserByProviderCredentials($provider, $accessToken): ?Authenticatable
    {
        $providerUser = Socialite::driver($provider)->userFromToken($accessToken);
        return $this->findOrCreateUser($provider, $providerUser);;
    }

    protected function findOrCreateUser($provider, ProviderUser $providerUser): ?Authenticatable
    {
        $user = User::where('email', $providerUser->email)->first();
        if(!empty($user)){
            if($user->google_id == null || $user->google_access_token == null) {
                $user->update(['google_id' => $providerUser->id, 'google_access_token' => $providerUser->token]);
            }
        }
        return $user;
    }
}