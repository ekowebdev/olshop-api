<?php

namespace App\Http\Models;

use App\Http\Models\Variant;
use App\Http\Models\ItemGift;
use App\Http\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ItemGiftImage extends BaseModel
{
    use HasFactory;

    protected $table = 'item_gift_images';
    protected $fillable = ['item_gift_id', 'variant_id', 'item_gift_image'];
    protected $appends = ['item_gift_image_url', 'item_gift_image_thumb_url'];

    public function getItemGiftImageUrlAttribute()
    {
        if ($this->item_gift_image != null) {
            $url = 'https://'. config('filesystems.disks.s3.bucket') .'.s3-'. config('filesystems.disks.s3.region') .'.amazonaws.com/images/' . $this->item_gift_image;
        }
        return $url ?? null;
    }

    public function getItemGiftImageThumbUrlAttribute()
    {
        if ($this->item_gift_image != null) {
            $url = 'https://'. config('filesystems.disks.s3.bucket') .'.s3-'. config('filesystems.disks.s3.region') .'.amazonaws.com/images/thumbnails/' . $this->item_gift_image;
        }
        return $url ?? null;
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
                    'item_gift_id', 
                    'variant_id', 
                    'item_gift_image',
                ]);
    }
}
