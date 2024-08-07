<?php

namespace App\Http\Models;

use App\Http\Models\Review;
use App\Http\Models\BaseModel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ReviewFile extends BaseModel
{
    use HasFactory;

    protected $connection = 'mysql';
    protected $table = 'review_files';
    protected $fillable = ['review_id', 'file'];
    protected $appends = ['file_url', 'file_thumbnail_url'];

    public function getFileUrlAttribute()
    {
        if ($this->file != null) {
            $file = explode('.', $this->file)[0];
            $url = config('services.cloudinary.path_url') . '/' . config('services.cloudinary.folder') . '/images/reviews/' . $file;
        }

        return $url ?? null;
    }

    public function getFileThumbnailUrlAttribute()
    {
        if ($this->file != null) {
            $file = explode('.', $this->file)[0];
            $url = config('services.cloudinary.path_url') . '/' . config('services.cloudinary.folder') . '/images/reviews/thumbnails/' . $file;
        }

        return $url ?? null;
    }

    public function reviews()
    {
        return $this->belongsTo(Review::class, 'review_id');
    }

    public function scopeGetAll($query)
    {
        return $query->select([
                    'id',
                    'review_id',
                    'file',
                ]);
    }
}
