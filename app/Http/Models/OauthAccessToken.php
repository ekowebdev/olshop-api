<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class OauthAccessToken extends BaseModel
{
    public $table = "oauth_access_tokens";
    protected $guarded = [];
    public $timestamps = false;
}