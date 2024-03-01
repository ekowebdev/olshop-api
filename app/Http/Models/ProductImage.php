<?php

namespace App\Http\Models;

use App\Http\Models\Variant;
use App\Http\Models\Product;
use App\Http\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductImage extends BaseModel
{
    use HasFactory;

    protected $table = 'product_images';
    protected $fillable = ['product_id', 'variant_id', 'image'];
    protected $appends = ['image_url', 'image_thumb_url'];

    public function getImageUrlAttribute()
    {
        if ($this->image != null) {
            $url = 'https://'. config('filesystems.disks.s3.bucket') .'.s3-'. config('filesystems.disks.s3.region') .'.amazonaws.com/images/' . $this->image;
        }
        return $url ?? null;
    }

    public function getImageThumbUrlAttribute()
    {
        if ($this->image != null) {
            $url = 'https://'. config('filesystems.disks.s3.bucket') .'.s3-'. config('filesystems.disks.s3.region') .'.amazonaws.com/images/thumbnails/' . $this->image;
        }
        return $url ?? null;
    }

    public function products()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function variants()
    {
        return $this->belongsTo(Variant::class, 'variant_id');
    }

    public function scopeGetAll($query)
    {      
        return $query->select([
                    'id',
                    'product_id', 
                    'variant_id', 
                    'image',
                ]);
    }
}
