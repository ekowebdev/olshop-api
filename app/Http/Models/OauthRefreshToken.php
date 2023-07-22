<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class OauthRefreshToken extends BaseModel
{
    public $table = "oauth_refresh_tokens";
    protected $guarded = [];
    public $timestamps = false;
}