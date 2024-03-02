<?php

namespace App\Http\Models;

use App\Http\Models\Cart;
use App\Http\Models\Product;
use App\Http\Models\BaseModel;
use App\Http\Models\ProductImage;
use App\Http\Models\OrderProduct;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Variant extends BaseModel
{
    use HasFactory;

    protected $table = 'variants';
    protected $fillable = ['product_id', 'name', 'slug', 'point', 'weight', 'quantity'];

    public function products()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function order_products()
    {
        return $this->hasMany(OrderProduct::class);
    }

    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    public function product_images()
    {
        return $this->hasOne(ProductImage::class);
    }

    public function getWeightAttribute($value)
    {
        return (int) $value;
    }

    public function scopeGetAll($query)
    {
        return $query->select([
                    'id', 
                    'product_id', 
                    'name',
                    'slug',
                    'quantity',
                    'point',
                    'weight',
                ]);
    }
}

