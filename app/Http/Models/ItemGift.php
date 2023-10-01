<?php

namespace App\Http\Models;

use App\Http\Models\Cart;
use App\Http\Models\Brand;
use App\Http\Models\Review;
use Illuminate\Support\Str;
use App\Http\Models\Variant;
use App\Http\Models\Category;
use App\Http\Models\BaseModel;
use App\Http\Models\Wishlists;
use App\Http\Models\ItemGiftImage;
use Illuminate\Support\Facades\DB;
use App\Http\Models\RedeemItemGift;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ItemGift extends BaseModel
{
    use HasFactory;

    protected $table = 'item_gifts';
    protected $fillable = ['item_gift_code', 'item_gift_name', 'category_id', 'brand_id', 'item_gift_slug', 'item_gift_description', 'item_gift_point', 'item_gift_weight', 'item_gift_quantity', 'item_gift_status'];

    public function item_gift_images()
    {
        return $this->hasMany(ItemGiftImage::class);
    }

    public function redeem_item_gifts()
    {
        return $this->hasMany(RedeemItemGift::class);
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function variants()
    {
        return $this->hasMany(Variant::class);
    }

    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    public function scopeGetAll($query)
    {   
        $user_id = auth()->user()->id ?? 0;
        return $query->select([
                    'id', 
                    'item_gift_code', 
                    'item_gift_name', 
                    'category_id', 
                    'brand_id', 
                    'item_gift_slug', 
                    'item_gift_description', 
                    'item_gift_point',
                    'item_gift_weight',
                    'item_gift_quantity',
                    'item_gift_status',
                    DB::raw('(SELECT COUNT(*) FROM reviews WHERE reviews.item_gift_id = item_gifts.id) AS total_reviews'),
                    DB::raw('(SELECT COALESCE(AVG(review_rating), 0) FROM reviews WHERE reviews.item_gift_id = item_gifts.id) AS total_rating'),
                    DB::raw("
                        (
                            SELECT COUNT(IFNULL(wishlists.item_gift_id, 0))
                            FROM wishlists
                            WHERE wishlists.item_gift_id = item_gifts.id
                            AND wishlists.user_id = ". $user_id ."
                        ) AS is_wishlist
                    "),
                ])
                ->from(DB::raw('item_gifts FORCE INDEX (index_item_gifts)'));
    }
}
