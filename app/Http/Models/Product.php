<?php

namespace App\Http\Models;

use Carbon\Carbon;
use Meilisearch\Client;
use App\Http\Models\Cart;
use App\Http\Models\Brand;
use App\Http\Models\Review;
use App\Http\Models\Variant;
use App\Http\Models\Category;
use App\Http\Models\Wishlist;
use Laravel\Scout\Searchable;
use App\Http\Models\BaseModel;
use App\Http\Models\OrderProduct;
use App\Http\Models\ProductImage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends BaseModel
{
    use HasFactory, Searchable;

    protected $connection = 'mysql';
    protected $primaryKey = 'id';
    protected $table = 'products';
    protected $fillable = ['id', 'code', 'name', 'category_id', 'brand_id', 'slug', 'description', 'spesification', 'point', 'weight', 'quantity', 'status'];
    protected $appends = ['is_wishlist'];

    public function product_images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function order_products()
    {
        return $this->hasMany(OrderProduct::class);
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function categories()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function brands()
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    public function variants()
    {
        return $this->hasMany(Variant::class);
    }

    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    public function shippings()
    {
        return $this->hasOne(Address::class);
    }

    public function getWeightAttribute($value)
    {
        return (int) $value;
    }

    public function getIsWishlistAttribute()
    {
        $user_id = (auth()->user()) ? auth()->user()->id : 0;

        $wishlists = Wishlist::where('user_id', $user_id)
            ->where('product_id', $this->getKey())
            ->get();

        return (count($wishlists) > 0) ? 1 : 0;
    }

    public function scopeGetAll($query)
    {
        return $query->select([
            'id',
            'code',
            'name',
            'category_id',
            'brand_id',
            'slug',
            'description',
            'spesification',
            'point',
            'weight',
            'quantity',
            'status',
            'total_review',
            'total_rating',
            'total_order'
        ])
        ->where('status', 'A')
        ->from(DB::raw('products FORCE INDEX (index_products)'));
    }

    public function toSearchableArray()
    {
        $image = $this->product_images()->where('is_primary', 1)->first();

        $data = [
            'code' => $this->code,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'spesification' => json_decode($this->spesification) ?? [],
            'category' => $this->categories ? [
                'name' => $this->categories->name,
                'slug' => $this->categories->slug,
            ] : null,
            'brand' => $this->brands ? [
                'name' => $this->brands->name,
                'slug' => $this->brands->slug,
            ] : null,
            'point' => (double) $this->point,
            'weight' => (float) $this->weight,
            'image' => $image ? $image->image_url : null,
        ];

        return $data;
    }
}
