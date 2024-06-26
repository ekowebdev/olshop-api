<?php

namespace App\Http\Models;

use App\Http\Models\Product;
use App\Http\Models\BaseModel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends BaseModel
{
    use HasFactory;

    protected $connection = 'mysql';
    protected $table = 'categories';
    protected $fillable = ['code', 'name', 'slug', 'image', 'sort', 'status'];
    protected $appends = ['image_url', 'image_thumbnail_url'];

    public function getImageUrlAttribute()
    {
        if ($this->image != null) {
            $url = Storage::disk('google')->url('images/category/' . $this->image);
        }
        return $url ?? null;
    }

    public function getImageThumbnailUrlAttribute()
    {
        if ($this->image != null) {
            $url = Storage::disk('google')->url('images/category/thumbnails/' . $this->image);
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
}

