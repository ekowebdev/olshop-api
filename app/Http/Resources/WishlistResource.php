<?php

namespace App\Http\Resources;

use App\Http\Resources\UserResource;
use Illuminate\Http\Resources\Json\JsonResource;

class WishlistResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'item_gifts' => [
                'id' => $this->item_gifts->id,
                'item_gift_code' => $this->item_gifts->item_gift_code,
                'item_gift_name' => $this->item_gifts->item_gift_name,
                'category' => ($this->item_gifts->category_id != null) ? $this->item_gifts->category->makeHidden(['created_at', 'updated_at']) : null,
                'brand' => ($this->item_gifts->brand_id != null) ? $this->item_gifts->brand->makeHidden(['created_at', 'updated_at']) : null,
                'item_gift_description' => $this->item_gifts->item_gift_description,
                'item_gift_point' => ($this->item_gifts->variants->count() > 0) 
                            ? array_unique([
                                min($this->item_gifts->variants->pluck('variant_point')->toArray()),
                                max($this->item_gifts->variants->pluck('variant_point')->toArray()),
                              ])
                            : [$this->item_gifts->item_gift_point],
                        'item_gift_quantity' => ($this->item_gifts->variants->count() > 0) 
                            ? $this->item_gifts->variants->sum('variant_quantity')
                            : $this->item_gifts->item_gift_quantity,
                'item_gift_status' => $this->item_gifts->item_gift_status,
                'item_gift_images' => $this->item_gifts->item_gift_images->map(function ($image) {
                    return [
                        'item_gift_id' => $image->item_gift_id,
                        'item_gift_image_url' => $image->item_gift_image_url,
                    ];
                }),
                'variants' => $this->item_gifts->variants->makeHidden(['created_at', 'updated_at']),
            ],
            'users' => $this->users->makeHidden(['created_at', 'updated_at']),
        ];
    }
}
