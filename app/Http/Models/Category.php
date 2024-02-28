<?php

namespace App\Http\Models;

use App\Http\Models\ItemGift;
use App\Http\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends BaseModel
{
    use HasFactory;

    protected $table = 'categories';
    protected $fillable = ['category_code', 'category_name', 'category_slug', 'category_image', 'category_sort', 'category_status'];
    protected $appends = ['category_image_url'];

    public function getCategoryImageUrlAttribute()
    {
        if ($this->category_image != null) {
            $url = 'https://'. config('filesystems.disks.s3.bucket') .'.s3-'. config('filesystems.disks.s3.region') .'.amazonaws.com/images/category/' . $this->category_image;
        }
        return $url ?? null;
    }

    public function item_gifts()
    {
        return $this->hasOne(ItemGift::class);
    }

    public function scopeGetAll($query)
    {
        return $query->select([
                    'id', 
                    'category_code', 
                    'category_name', 
                    'category_slug', 
                    'category_sort',
                    'category_status',
                    'category_image',
                ]);
    }
}

