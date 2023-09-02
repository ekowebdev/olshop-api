<?php

namespace App\Http\Models;

use Illuminate\Support\Str;
use App\Http\Models\ItemGift;
use App\Http\Models\BaseModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Variant extends BaseModel
{
    use HasFactory;

    protected $table = 'variants';
    protected $fillable = ['item_gift_id', 'variant_name', 'variant_point', 'variant_quantity'];

    public function item_gifts()
    {
        return $this->belongsTo(ItemGift::class, 'item_gift_id');
    }

    public function scopeGetAll($query)
    {
        return $query->select([
                    'id', 
                    'item_gift_id', 
                    'variant_name', 
                    'variant_point', 
                    'variant_quantity'
                ]);
    }
}

