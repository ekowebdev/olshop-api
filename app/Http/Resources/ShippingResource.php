<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class ShippingResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'redeems' => [
                'redeem_id' => $this->redeems->id,
                'redeem_code' => $this->redeems->redeem_code,
                'total_point' => $this->redeems->total_point,
                'redeem_date' => $this->redeems->redeem_date,
                'fredeem_date' => Carbon::parse($this->redeems->created_at)->diffForHumans(),
                'note' => $this->redeems->note,
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
                            'item_gift_spesification' => json_decode($redeem_item_gift->item_gifts->item_gift_spesification),
                            'item_gift_point' => $redeem_item_gift->item_gifts->item_gift_point ?? 0,
                            'fitem_gift_point' => $this->format_item_gift_point($redeem_item_gift),
                            'item_gift_weight' => $redeem_item_gift->item_gifts->item_gift_weight ?? 0,
                            'fitem_gift_weight' => ($redeem_item_gift->item_gifts->item_gift_weight == null) ? '0 Gram' : $redeem_item_gift->item_gifts->item_gift_weight . ' Gram',
                            'item_gift_status' => $redeem_item_gift->item_gifts->item_gift_status,
                            'item_gift_images' => $redeem_item_gift->item_gifts->item_gift_images->map(function ($image) {
                                return [
                                    'item_gift_id' => $image->item_gift_id,
                                    'item_gift_image_url' => $image->item_gift_image_url,
                                    'item_gift_image_thumbnail_url' => $image->item_gift_image_thumb_url,
                                ];
                            }),
                        ],
                        'variants' => ($redeem_item_gift->variants) 
                            ? [
                                'id' => $redeem_item_gift->variants->id,
                                'variant_name' => $redeem_item_gift->variants->variant_name,
                                'variant_quantity' => $redeem_item_gift->variants->variant_quantity,
                                'variant_point' => $redeem_item_gift->variants->variant_point,
                                'fvariant_point' => format_money(strval($redeem_item_gift->variants->variant_point)),
                            ] : null,
                    ];
                })
            ],
            'origin' => [
                'id' => $this->city_origin->city_id,
                'city_name' => $this->city_origin->city_name
            ],
            'destination' => [
                'id' => $this->city_destination->city_id,
                'city_name' => $this->city_destination->city_name
            ],
            'weight' => $this->weight,
            'fweight' => $this->weight . ' Gram',
            'courier' => $this->courier,
            'service' => $this->service,
            'description' => $this->description,
            'cost' => $this->cost,
            'etd' => $this->etd,
        ];
    }

    private function format_item_gift_point($item)
    {
        $variant_points = $item->item_gifts->variants->pluck('variant_point')->toArray();
        
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
            return format_money(strval($item->item_gifts->item_gift_point ?? 0));
        }
    }
}
