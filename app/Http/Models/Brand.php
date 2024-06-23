<?php

namespace App\Http\Models;

use App\Http\Models\Product;
use App\Http\Models\BaseModel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Brand extends BaseModel
{
    use HasFactory;

    protected $connection = 'mysql';
    protected $table = 'brands';
    protected $fillable = ['name', 'slug', 'logo', 'sort'];
    protected $appends = ['logo_url', 'logo_thumbnail_url'];

    public function getLogoUrlAttribute()
    {
        if ($this->logo != null) {
            $url = Storage::disk('google')->url('images/brand/' . $this->logo);
        }
        return $url ?? null;
    }

    public function getLogoThumbnailUrlAttribute()
    {
        if ($this->logo != null) {
            $url = Storage::disk('google')->url('images/brand/thumbnails/' . $this->logo);
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
                    'name',
                    'slug',
                    'sort',
                    'logo',
                ]);
    }
}

