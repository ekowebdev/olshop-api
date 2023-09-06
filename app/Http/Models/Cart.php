<?php

namespace App\Http\Models;

use App\Http\Models\User;
use Illuminate\Support\Str;
use App\Http\Models\Variant;
use App\Http\Models\ItemGift;
use App\Http\Models\BaseModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cart extends BaseModel
{
    use HasFactory;

    protected $table = 'carts';
    protected $fillable = ['user_id', 'item_gift_id', 'variant_id', 'cart_quantity'];

    public function users()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function item_gifts()
    {
        return $this->belongsTo(ItemGift::class, 'item_gift_id');
    }

    public function variants()
    {
        return $this->belongsTo(Variant::class, 'variant_id');
    }

    public function scopeGetAll($query)
    {
        return $query->select([
                    'id', 
                    'user_id', 
                    'item_gift_id', 
                    'variant_id',
                    'cart_quantity',
                ])
                ->from(DB::raw('carts FORCE INDEX (index_carts)'));
    }
}
