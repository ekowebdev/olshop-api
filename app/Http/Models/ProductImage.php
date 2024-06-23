<?php

namespace App\Http\Models;

use App\Http\Models\Product;
use App\Http\Models\Variant;
use App\Http\Models\BaseModel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductImage extends BaseModel
{
    use HasFactory;

    protected $connection = 'mysql';
    protected $table = 'product_images';
    protected $fillable = ['product_id', 'variant_id', 'image'];
    protected $appends = ['image_url', 'image_thumbnail_url'];

    public function getImageUrlAttribute()
    {
        if ($this->image != null) {
            $url = Storage::disk('google')->url('images/' . $this->image);
        }
        return $url ?? null;
    }

    public function getImageThumbnailUrlAttribute()
    {
        if ($this->image != null) {
            $url = Storage::disk('google')->url('images/thumbnails/' . $this->image);
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
