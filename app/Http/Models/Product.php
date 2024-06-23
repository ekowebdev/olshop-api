<?php

namespace App\Http\Models;

use App\Http\Models\Cart;
use App\Http\Models\Brand;
use App\Http\Models\Review;
use App\Http\Models\Variant;
use App\Http\Models\Category;
use App\Http\Models\Wishlist;
use App\Http\Models\BaseModel;
use App\Http\Models\ProductImage;
use Illuminate\Support\Facades\DB;
use App\Http\Models\OrderProduct;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends BaseModel
{
    use HasFactory;

    protected $connection = 'mysql';
    protected $primaryKey = 'id';
    protected $table = 'products';
    protected $fillable = ['id', 'code', 'name', 'category_id', 'brand_id', 'slug', 'description', 'spesification', 'point', 'weight', 'quantity', 'status'];
    protected $appends = ['total_review', 'total_rating', 'total_order', 'is_wishlist'];

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

    public function getTotalReviewAttribute()
    {
        $total_review = Review::where('product_id', $this->getKey())->groupBy('user_id', 'product_id')->count();
        return $total_review;
    }

    public function getTotalRatingAttribute()
    {
        $user_item_avg_ratings = Review::select('user_id', 'product_id', DB::raw('AVG(rating) as avg_rating'))
            ->where('product_id', $this->getKey())
            ->groupBy('user_id', 'product_id')
            ->get();
        $total_avg_rating = $user_item_avg_ratings->pluck('avg_rating')->avg();
        return round($total_avg_rating, 1);
    }

    public function getTotalOrderAttribute()
    {
        $total_order = OrderProduct::selectRaw('SUM(quantity) AS total_order')->where('product_id', $this->getKey())->first()->total_order;
        return $total_order;
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
                    DB::raw('(SELECT COUNT(*) FROM reviews WHERE reviews.product_id = products.id LIMIT 1) AS total_review'),
                    DB::raw('(SELECT ROUND(AVG(rating), 1) FROM reviews WHERE reviews.product_id = products.id LIMIT 1) AS total_rating'),
                    DB::raw('(SELECT SUM(order_products.quantity) FROM order_products WHERE order_products.product_id = products.id) AS total_order'),
                ])
                ->where('status', 'A')
                ->from(DB::raw('products FORCE INDEX (index_products)'));
    }
}
