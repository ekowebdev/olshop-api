<?php

namespace App\Http\Models;

use App\Http\Models\User;
use App\Http\Models\BaseModel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Profile extends BaseModel
{
    use HasFactory;

    protected $connection = 'mysql';
    protected $table = 'profiles';
    protected $fillable = ['user_id', 'name', 'birthdate', 'phone_number', 'avatar'];
    protected $appends = ['avatar_url', 'avatar_thumbnail_url'];

    public function getAvatarUrlAttribute()
    {
        if ($this->avatar != null) {
            $url = Storage::disk('google')->url('images/avatar/' . $this->avatar);
        }
        return $url ?? null;
    }

    public function getAvatarThumbnailUrlAttribute()
    {
        if ($this->avatar != null) {
            $url = Storage::disk('google')->url('images/avatar/thumbnails/' . $this->avatar);
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

