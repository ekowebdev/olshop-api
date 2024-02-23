<?php

namespace App\Http\Models;

use App\Http\Models\User;
use App\Http\Models\Redeem;
use App\Http\Models\ItemGift;
use App\Http\Models\BaseModel;
use App\Http\Models\ReviewFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Review extends BaseModel
{
    use HasFactory;

    protected $table = 'reviews';
    protected $fillable = ['user_id', 'redeem_id', 'item_gift_id', 'review_text', 'review_rating', 'review_date'];
    protected $appends = ['has_files'];

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

    public function review_files()
    {
        return $this->hasMany(ReviewFile::class);
    }

    public function getHasFilesAttribute()
    {
        $has_files = Review::review_files()->where('review_id', $this->getKey())->count();
        return $has_files > 0 ? 'yes' : 'no';
    }

    public function scopeGetAll($query)
    {
        return $query->select(
                'reviews.id', 
                'reviews.user_id', 
                'reviews.redeem_id', 
                'reviews.item_gift_id', 
                'reviews.review_text', 
                'reviews.review_rating', 
                'reviews.review_date', 
                'reviews.created_at',
                DB::raw('
                    (
                        CASE 
                            WHEN EXISTS (SELECT 1 FROM review_files WHERE review_files.review_id = reviews.id) THEN "yes"
                            ELSE "no"
                        END
                    ) AS has_files
                '),   
            )
            ->joinSub($this->lastReviews(), 'last_reviews', function ($join) {
                $join->on('reviews.user_id', '=', 'last_reviews.user_id')
                    ->on('reviews.item_gift_id', '=', 'last_reviews.item_gift_id')
                    ->on('reviews.created_at', '=', 'last_reviews.last_created_at');
            })
            ->where('reviews.user_id', '=', DB::raw('last_reviews.user_id'));
    }

    private function lastReviews()
    {
        return DB::table('reviews')
            ->select('user_id', 'item_gift_id', DB::raw('MAX(created_at) as last_created_at'))
            ->groupBy('user_id', 'item_gift_id');
    }
}
