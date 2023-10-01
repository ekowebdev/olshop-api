<?php

namespace App\Http\Resources;

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
                        'item_gift_weight' => $redeem_item_gift->item_gifts->item_gift_weight ?? 0,
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
            'metadata' => json_decode($this->metadata),
            'redeem_status' => $this->redeem_status,
            'users' => [
                'id' => $this->users->id,
                'name' => $this->users->name,
                'roles' => $this->users->getRoleNames(),
                'username' => $this->users->username,
                'email' => $this->users->email,
                'birthdate' => $this->users->birthdate,
                'address' => ($this->users->address) ? [
                    'province_id' => $this->users->address->province_id,
                    'city_id' => $this->users->address->city_id,
                    'district_id' => $this->users->address->district_id,
                    'postal_code' => $this->users->address->postal_code,
                    'address' => $this->users->address->address,
                ] : null,
            ]
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
