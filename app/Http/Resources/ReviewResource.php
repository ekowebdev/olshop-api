<?php

namespace App\Http\Resources;

use App\Http\Resources\UserResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'review_text' => $this->review_text,
            'review_rating' => $this->review_rating,
            'review_date' => $this->review_date,
            'item_gifts' => [
                'id' => $this->item_gifts->id,
                'item_gift_code' => $this->item_gifts->item_gift_code,
                'item_gift_name' => $this->item_gifts->item_gift_name,
                'category' => ($this->item_gifts->category_id != null) ? $this->item_gifts->category->makeHidden(['created_at', 'updated_at']) : null,
                'brand' => ($this->item_gifts->brand_id != null) ? $this->item_gifts->brand->makeHidden(['created_at', 'updated_at']) : null,
                'item_gift_description' => $this->item_gifts->item_gift_description,
                'item_gift_point' => $this->item_gifts->item_gift_point ?? 0,
                'fitem_gift_point' => $this->formatFitemGiftPoint($this->item_gifts),
                'item_gift_quantity' => $this->item_gifts->item_gift_quantity ?? 0,
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

    private function formatFitemGiftPoint($item)
    {
        $variantPoints = $item->variants->pluck('variant_point')->toArray();
        
        if (count($variantPoints) == 1) {
            return strval($variantPoints[0]);
        } elseif (count($variantPoints) > 1) {
            $minValue = min($variantPoints);
            $maxValue = max($variantPoints);

            if ($minValue === $maxValue) {
                return strval($minValue);
            }

            return "{$minValue} ~ {$maxValue}";
        } else {
            return strval($item->item_gift_point ?? 0);
        }
    }
}
