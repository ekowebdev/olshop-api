<?php

namespace App\Http\Models;

use App\Http\Models\ItemGift;
use App\Http\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ItemGiftImage extends BaseModel
{
    use HasFactory;

    protected $table = 'item_gift_images';
    protected $fillable = ['item_gift_id', 'item_gift_image'];
    protected $appends = ['item_gift_image_url', 'item_gift_image_thumb_url'];

    public function getItemGiftImageUrlAttribute()
    {
        if ($this->item_gift_image != null) {
            $url = 'https://'. env('AWS_BUCKET') .'.s3-'. env('AWS_DEFAULT_REGION') .'.amazonaws.com/images/' . $this->item_gift_image;
        }
        return $url ?? null;
    }

    public function getItemGiftImageThumbUrlAttribute()
    {
        if ($this->item_gift_image != null) {
            $url = 'https://'. env('AWS_BUCKET') .'.s3-'. env('AWS_DEFAULT_REGION') .'.amazonaws.com/images/thumbnails/' . $this->item_gift_image;
        }
        return $url ?? null;
    }

    public function scopeGetAll($query)
    {      
        return $query->select([
                    'id',
                    'item_gift_id', 
                    'item_gift_image',
                ]);
    }

    public function item_gifts()
    {
        return $this->belongsTo(ItemGift::class, 'item_gift_id');
    }
}
