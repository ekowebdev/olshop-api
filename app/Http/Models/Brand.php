<?php

namespace App\Http\Models;

use App\Http\Models\Product;
use Laravel\Scout\Searchable;
use App\Http\Models\BaseModel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Brand extends BaseModel
{
    use HasFactory, Searchable;

    protected $connection = 'mysql';
    protected $table = 'brands';
    protected $fillable = ['name', 'slug', 'logo', 'sort'];
    protected $appends = ['logo_url', 'logo_thumbnail_url'];

    public function getLogoUrlAttribute()
    {
        if ($this->logo != null) {
            $logo = explode('.', $this->logo)[0];
            $url = config('services.cloudinary.path_url') . '/' . config('services.cloudinary.folder') . '/images/brands/' . $logo;
        }

        return $url ?? null;
    }

    public function getLogoThumbnailUrlAttribute()
    {
        if ($this->logo) {
            $logo = explode('.', $this->logo)[0];
            $url = config('services.cloudinary.path_url') . '/' . config('services.cloudinary.folder') . '/images/brands/thumbnails/' . $logo . '_thumb';
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

    public function toSearchableArray()
    {
        $data = [
            'name' => $this->name,
            'slug' => $this->slug,
        ];

        return $data;
    }
}

