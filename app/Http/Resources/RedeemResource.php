<?php

namespace App\Http\Resources;

use App\Http\Resources\UserResource;
use Illuminate\Http\Resources\Json\JsonResource;

class RedeemResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'redeem_code' => $this->redeem_code,
            'redeem_item_gifts' => $this->redeem_item_gifts->map(function ($redeem_item_gift) {
                return [
                    'redeem_id' => $redeem_item_gift->redeem_id,
                    'redeem_quantity' => $redeem_item_gift->redeem_quantity,
                    'redeem_point' => $redeem_item_gift->redeem_point,
                    'item_gifts' => [
                        'id' => $redeem_item_gift->item_gifts->id,
                        'item_gift_code' => $redeem_item_gift->item_gifts->item_gift_code,
                        'item_gift_name' => $redeem_item_gift->item_gifts->item_gift_name,
                        'category' => ($redeem_item_gift->item_gifts->category_id != null) ? $redeem_item_gift->item_gifts->category->makeHidden(['created_at', 'updated_at']) : null,
                        'brand' => ($redeem_item_gift->item_gifts->brand_id != null) ? $redeem_item_gift->item_gifts->brand->makeHidden(['created_at', 'updated_at']) : null,
                        'item_gift_description' => $redeem_item_gift->item_gifts->item_gift_description,
                        'fitem_gift_point' => $this->formatFitemGiftPoint($redeem_item_gift->item_gifts),
                        'item_gift_status' => $redeem_item_gift->item_gifts->item_gift_status,
                        'item_gift_images' => $redeem_item_gift->item_gifts->item_gift_images->map(function ($image) {
                            return [
                                'item_gift_id' => $image->item_gift_id,
                                'item_gift_image_url' => $image->item_gift_image_url,
                            ];
                        }),
                    ],
                    'variants' => ($redeem_item_gift->item_gifts->variants->count() > 0) 
                        ? [
                            'id' => $redeem_item_gift->variants->id,
                            'variant_name' => $redeem_item_gift->variants->variant_name,
                            'variant_point' => $redeem_item_gift->variants->variant_point,
                        ] : null,
                ];
            }),
            'total_point' => $this->total_point,
            'redeem_date' => $this->redeem_date,
            'snap_url' => $this->snap_url,
            'metadata' => $this->metadata,
            'users' => $this->users->makeHidden(['created_at', 'updated_at']),
            'redeem_status' => $this->redeem_status,
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
