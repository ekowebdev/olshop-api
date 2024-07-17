<?php

namespace App\Http\Models;

use App\Http\Models\BaseModel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Slider extends BaseModel
{
    use HasFactory;

    protected $connection = 'mysql';
    protected $table = 'sliders';
    protected $fillable = ['image', 'title', 'description', 'link', 'sort', 'start_date', 'end_date', 'status'];
    protected $appends = ['image_url', 'image_thumbnail_url'];

    public function getImageUrlAttribute()
    {
        if ($this->image != null) {
            $image = explode('.', $this->image)[0];
            $url = config('services.cloudinary.path_url') . '/' . config('services.cloudinary.folder') . '/images/sliders/' . $image;
        }

        return $url ?? null;
    }

    public function getImageThumbnailUrlAttribute()
    {
        if ($this->image != null) {
            $image = explode('.', $this->image)[0];
            $url = config('services.cloudinary.path_url') . '/' . config('services.cloudinary.folder') . '/images/sliders/thumbnails/' . $image;
        }

        return $url ?? null;
    }

    public function scopeGetAll($query)
    {
        return $query->select([
                    'id',
                    'image',
                    'title',
                    'description',
                    'link',
                    'sort',
                    'start_date',
                    'end_date',
                    'status',
                ])
                ->where('status', '=', 'A');
    }

    public function scopeGetAllActive($query)
    {
        return $query->select([
                    'id',
                    'image',
                    'title',
                    'description',
                    'link',
                    'sort',
                    'start_date',
                    'end_date',
                    'status',
                ])
                ->where('start_date', '<=', now()->format('Y-m-d'))
                ->where('end_date', '>=', now()->format('Y-m-d'))
                ->where('status', '=', 'A');
    }
}

