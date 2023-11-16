<?php

namespace App\Http\Models;

use App\Http\Models\User;
use App\Http\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Profile extends BaseModel
{
    use HasFactory;

    protected $table = 'profiles';
    protected $fillable = ['user_id', 'name', 'birthdate', 'phone_number', 'avatar'];
    protected $appends = ['avatar_url'];

    public function getAvatarUrlAttribute()
    {
        if ($this->avatar != null) {
            $url = 'https://'. env('AWS_BUCKET') .'.s3-'. env('AWS_DEFAULT_REGION') .'.amazonaws.com/images/avatar/' . $this->avatar;
        }
        return $url ?? null;
    }

    public function users()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeGetAll($query)
    {
        return $query->select([
                    'id', 
                    'user_id', 
                    'name',
                    'birthdate', 
                    'phone_number', 
                    'avatar',
                ]);
    }
}

