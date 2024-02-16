<?php

namespace App\Http\Models;

use App\Http\Models\User;
use App\Http\Models\Redeem;
use App\Http\Models\ItemGift;
use App\Http\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class Review extends BaseModel
{
    use HasFactory;

    protected $table = 'reviews';
    protected $fillable = ['user_id', 'redeem_id', 'item_gift_id', 'review_text', 'review_rating', 'review_date'];

    public function users()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function item_gifts()
    {
        return $this->belongsTo(ItemGift::class, 'item_gift_id');
    }

    public function redeems()
    {
        return $this->belongsTo(Redeem::class, 'redeem_id');
    }

    public function scopeGetAll($query)
    {
        return $query->select('reviews.id', 'reviews.user_id', 'reviews.redeem_id', 'reviews.item_gift_id', 'reviews.review_text', 'reviews.review_rating', 'reviews.review_date', 'reviews.created_at')
            ->joinSub($this->lastReviews(), 'last_reviews', function ($join) {
                $join->on('reviews.user_id', '=', 'last_reviews.user_id')
                    ->on('reviews.item_gift_id', '=', 'last_reviews.item_gift_id')
                    ->on('reviews.created_at', '=', 'last_reviews.last_created_at');
            });
    }

    private function lastReviews()
    {
        return DB::table('reviews')
            ->select('user_id', 'item_gift_id', DB::raw('MAX(created_at) as last_created_at'))
            ->groupBy('user_id', 'item_gift_id');
    }
}
