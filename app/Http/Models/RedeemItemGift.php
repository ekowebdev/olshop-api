<?php

namespace App\Http\Models;

use App\Redeem;
use App\Http\Models\ItemGift;
use App\Http\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RedeemItemGift extends BaseModel
{
    use HasFactory;

    protected $table = 'redeem_item_gifts';
    protected $fillable = ['redeem_id', 'item_gift_id', 'redeem_quantity', 'redeem_point'];

    public function redeems()
    {
        return $this->belongsTo(Redeem::class, 'redeem_id');
    }

    public function item_gifts()
    {
        return $this->belongsTo(ItemGift::class, 'item_gift_id');
    }
}
