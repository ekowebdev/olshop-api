<?php 

namespace App\Http\Repositories;

use Carbon\Carbon;
use App\Http\Models\OauthAccessToken;
use App\Http\Models\OauthRefreshToken;

class OauthRepository extends BaseRepository {

    private $repository_name = 'Oauth';
    private $model_access_token, $model_refresh_token;

	public function __construct(OauthAccessToken $model_access_token, OauthRefreshToken $model_refresh_token)
	{
        $this->model_access_token = $model_access_token;
        $this->model_refresh_token = $model_refresh_token;
	}
   
    public function checkRefreshToken($refreshToken, $accessToken)
    {
        $date_now = Carbon::now();
        $today = strtotime($date_now->format('Y-m-d H:i:s'));

        $data = $this->model_refresh_token
                ->where('id', $refreshToken)
                ->where('access_token_id', $accessToken)    
                ->where('revoked', 0)
                ->where('expires_at', '>', Carbon::now())                    
                ->first();

        return $data;
    }

    public function checkAccessToken($accessToken)
    {
        $date_now = Carbon::now();
        $today = strtotime($date_now->format('Y-m-d H:i:s'));

        $data = $this->model_access_token
                ->where('id', $accessToken)
                ->where('revoked', 0)
                ->where('expires_at', '<' , Carbon::now())                    
                ->first();

        return $data;
    }
}