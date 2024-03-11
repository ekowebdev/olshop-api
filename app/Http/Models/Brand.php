<?php

namespace App\Http\Models;

use App\Http\Models\Product;
use App\Http\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Brand extends BaseModel
{
    use HasFactory;

    protected $table = 'brands';
    protected $fillable = ['name', 'slug', 'logo', 'sort'];
    protected $appends = ['logo_url', 'logo_thumbnail_url'];

    public function getLogoUrlAttribute()
    {
        if ($this->logo != null) {
            $url = 'https://'. config('filesystems.disks.s3.bucket') .'.s3-'. config('filesystems.disks.s3.region') .'.amazonaws.com/images/brand/' . $this->logo;
        }
        return $url ?? null;
    }

    public function getLogoThumbnailUrlAttribute()
    {
        if ($this->logo != null) {
            $url = 'https://'. config('filesystems.disks.s3.bucket') .'.s3-'. config('filesystems.disks.s3.region') .'.amazonaws.com/images/brand/thumbnails/' . $this->logo;
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

