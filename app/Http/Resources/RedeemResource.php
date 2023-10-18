<?php

namespace App\Http\Resources;

use Carbon\Carbon;
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
            }),
            'total_point' => $this->total_point,
            'shipping_fee' => intval($this->shipping_fee),
            'total_amount' => $this->total_amount,
            'redeem_date' => $this->redeem_date,
            'note' => $this->note,
            'fredeem_date' => Carbon::parse($this->created_at)->diffForHumans(),
            'snap_url' => $this->snap_url,
            'metadata' => json_decode($this->metadata),
            'redeem_status' => $this->redeem_status,
            'shippings' => [
                'id' => $this->shippings->id,
                'shipping_origin' => [
                    'id' => $this->shippings->city_origin->city_id,
                    'city_name' => $this->shippings->city_origin->city_name
                ],
                'shipping_destination' => [
                    'id' => $this->shippings->city_destination->city_id,
                    'city_name' => $this->shippings->city_destination->city_name
                ],
                'shipping_weight' => (int) $this->shippings->weight,
                'fshipping_weight' => (int) $this->shippings->weight . ' Gram',
                'shipping_courier' => $this->shippings->courier,
                'shipping_service' => $this->shippings->service,
                'shipping_description' => $this->shippings->description,
                'shipping_cost' => $this->shippings->cost,
                'shipping_etd' => $this->shippings->etd,
            ],
            'users' => [
                'id' => $this->users->id,
                'name' => $this->users->name,
                'roles' => $this->users->getRoleNames(),
                'username' => $this->users->username,
                'email' => $this->users->email,
                'profile' => ($this->users->profile) ? [
                    'id' => $this->users->profile->id,
                    'birthdate' => $this->users->profile->birthdate,
                    'phone_number' => $this->users->profile->phone_number,
                    'avatar' => $this->users->profile->avatar,
                    'avatar_url' => $this->users->profile->avatar_url,
                ] : null,
                'address' => ($this->address) ? [
                    'id' => $this->address->id,
                    'person_name' => $this->address->person_name,
                    'person_phone' => $this->address->person_phone,
                    'province' => [
                        'id' => $this->address->province->province_id,
                        'province_name' => $this->address->province->province_name
                    ],
                    'city' => [
                        'id' => $this->address->city->city_id,
                        'city_name' => $this->address->city->city_name
                    ],
                    'subdistrict' => [
                        'id' => $this->address->subdistrict->subdistrict_id,
                        'subdistrict_name' => $this->address->subdistrict->subdistrict_name
                    ],
                    'postal_code' => $this->address->postal_code,
                    'address' => $this->address->address,
                    'is_main' => ($this->users->main_address_id == $this->address->id) ? 1 : 0,
                ] : null,
            ]
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
