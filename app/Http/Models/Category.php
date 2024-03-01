<?php

namespace App\Http\Models;

use App\Http\Models\Product;
use App\Http\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends BaseModel
{
    use HasFactory;

    protected $table = 'categories';
    protected $fillable = ['code', 'name', 'slug', 'image', 'sort', 'status'];
    protected $appends = ['image_url'];

    public function getImageUrlAttribute()
    {
        if ($this->image != null) {
            $url = 'https://'. config('filesystems.disks.s3.bucket') .'.s3-'. config('filesystems.disks.s3.region') .'.amazonaws.com/images/category/' . $this->image;
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

