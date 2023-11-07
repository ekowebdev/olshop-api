<?php

namespace App\Http\Models;

use App\Http\Models\BaseModel;

class OauthRefreshToken extends BaseModel
{
    public $table = "oauth_refresh_tokens";
    protected $guarded = [];
    public $timestamps = false;
}