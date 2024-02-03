<?php

namespace App\Http\Models;

use App\Http\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PasswordReset extends BaseModel
{
    use HasFactory;

    protected $primaryKey = 'email';
    protected $table = 'password_resets';
    protected $fillable = ['email', 'token', 'created_at'];
    public $timestamps = false;
}
