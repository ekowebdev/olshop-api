<?php

namespace App\Http\Repositories;

use Carbon\Carbon;
use App\Http\Models\OauthAccessToken;
use App\Http\Models\OauthRefreshToken;

class OauthRepository extends BaseRepository {

    private $repository = 'Oauth';
    private $modelAccessToken, $modelRefreshToken;

	public function __construct(OauthAccessToken $modelAccessToken, OauthRefreshToken $modelRefreshToken)
	{
        $this->modelAccessToken = $modelAccessToken;
        $this->modelRefreshToken = $modelRefreshToken;
	}

    public function checkRefreshToken($refreshToken, $accessToken)
    {
        $data = $this->modelRefreshToken
                    ->where('id', $refreshToken)
                    ->where('access_token_id', $accessToken)
                    ->where('revoked', 0)
                    ->where('expires_at', '>', Carbon::now())
                    ->first();
        return $data;
    }

    public function checkAccessToken($accessToken)
    {
        $data = $this->modelAccessToken
                    ->where('id', $accessToken)
                    ->where('revoked', 0)
                    ->where('expires_at', '<' , Carbon::now())
                    ->first();
        return $data;
    }
}
