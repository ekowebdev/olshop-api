<?php

namespace App\Http\Models;

use App\Http\Models\Cart;
use App\Http\Models\Brand;
use App\Http\Models\Redeem;
use App\Http\Models\Review;
use Illuminate\Support\Str;
use App\Http\Models\Variant;
use App\Http\Models\Category;
use App\Http\Models\BaseModel;
use App\Http\Models\Wishlists;
use App\Http\Models\ShippingImage;
use Illuminate\Support\Facades\DB;
use App\Http\Models\RedeemShipping;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Shipping extends BaseModel
{
    use HasFactory;

    protected $table = 'shippings';
    protected $fillable = ['redeem_id', 'origin', 'destination', 'weight', 'courier', 'cost'];

    public function redeem()
    {
        return $this->belongsTo(Redeem::class);
    }

    public function scopeGetAll($query)
    {
        return $query->select([
                    'id', 
                    'redeem_id', 
                    'origin', 
                    'destination', 
                    'weight', 
                    'courier', 
                    'cost'
                ]);
    }
}
