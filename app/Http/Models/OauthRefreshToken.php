<?php

namespace App\Http\Models;

use App\Http\Models\BaseModel;

class OauthRefreshToken extends BaseModel
{
    protected $connection = 'mysql';
    protected $table = "oauth_refresh_tokens";
    protected $guarded = [];
    public $timestamps = false;
}
