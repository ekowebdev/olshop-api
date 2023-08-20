<?php

namespace App\Http\Models;

use App\Http\Models\User;
use App\Http\Models\ItemGift;
use App\Http\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Review extends BaseModel
{
    use HasFactory;

    protected $table = 'reviews';
    protected $fillable = ['user_id', 'item_gift_id', 'review_text', 'review_rating', 'review_date'];

    public function scopeGetAll($query)
    {      
        return $query->select([
                    'id', 
                    'user_id', 
                    'item_gift_id', 
                    'review_text', 
                    'review_rating', 
                    'review_date'
                ])
                ->where('user_id', auth()->user()->id);
    }

    public function users()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function item_gifts()
    {
        return $this->belongsTo(ItemGift::class, 'item_gift_id');
    }
}
