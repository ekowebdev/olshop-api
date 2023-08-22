<?php

namespace App\Http\Models;

use App\Http\Models\User;
use App\Http\Models\ItemGift;
use App\Http\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Wishlist extends BaseModel
{
    use HasFactory;

    protected $table = 'wishlists';
    protected $fillable = ['user_id', 'item_gift_id'];

    public function scopeGetAll($query)
    {      
        return $query->select([
                    'id', 
                    'user_id', 
                    'item_gift_id'
                ]);
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
