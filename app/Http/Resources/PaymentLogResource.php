<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PaymentLogResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'payment_type' => $this->payment_type,
            'redeems' => [
                'redeem_id' => $this->redeems->id,
                'redeem_code' => $this->redeems->redeem_code,
                'total_point' => $this->redeems->total_point,
                'redeem_date' => $this->redeems->redeem_date,
                'snap_url' => $this->redeems->snap_url,
                'metadata' => json_decode($this->redeems->metadata),
                'redeem_status' => $this->redeems->redeem_status,
                'redeem_item_gifts' => $this->redeems->redeem_item_gifts->map(function ($redeem_item_gift){
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
                })
            ],
            'raw_response' => json_decode($this->raw_response),
            'payment_status' => $this->payment_status,
        ];
    }

    private function formatFitemGiftPoint($item)
    {
        $variant_points = $item->variants->pluck('variant_point')->toArray();
        
        if (count($variant_points) == 1) {
            return strval($variant_points[0]);
        } elseif (count($variant_points) > 1) {
            $min_value = min($variant_points);
            $max_value = max($variant_points);

            if ($min_value === $max_value) {
                return strval($min_value);
            }
            
            return format_money($min_value) . " ~ " . format_money($max_value);
        } else {
            return format_money(strval($this->item_gift_point ?? 0));
        }
    }
}
