<?php

namespace App\Http\Models;

use App\Http\Models\Product;
use Laravel\Scout\Searchable;
use App\Http\Models\BaseModel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends BaseModel
{
    use HasFactory, Searchable;

    protected $connection = 'mysql';
    protected $table = 'categories';
    protected $fillable = ['code', 'name', 'slug', 'image', 'sort', 'status'];
    protected $appends = ['image_url', 'image_thumbnail_url'];

    public function getImageUrlAttribute()
    {
        if ($this->image != null) {
            $image = explode('.', $this->image)[0];
            $url = config('services.cloudinary.path_url') . '/' . config('services.cloudinary.folder') . '/images/categories/' . $image;
        }

        return $url ?? null;
    }

    public function getImageThumbnailUrlAttribute()
    {
        if ($this->image) {
            $image = explode('.', $this->image)[0];
            $url = config('services.cloudinary.path_url') . '/' . config('services.cloudinary.folder') . '/images/categories/thumbnails/' . $image . '_thumb';
        }

        return $url ?? null;
    }

    public function products()
    {
        return $this->hasOne(Product::class);
    }

    public function scopeGetAll($query)
    {
        return $query->select([
                    'id',
                    'code',
                    'name',
                    'slug',
                    'sort',
                    'status',
                    'image',
                ]);
    }

    public function toSearchableArray()
    {
        $data = [
            'code' => $this->code,
            'name' => $this->name,
            'slug' => $this->slug,
        ];

        return $data;
    }
}

