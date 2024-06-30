<?php

namespace App\Http\Models;

use App\Http\Models\BaseModel;

class OauthAccessToken extends BaseModel
{
    protected $connection = 'mysql';
    protected $table = "oauth_access_tokens";
    protected $guarded = [];
    public $timestamps = false;
}
