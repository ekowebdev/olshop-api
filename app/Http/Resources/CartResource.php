<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
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
                'fitem_gift_point' => $this->format_item_gift_point($this->item_gifts),
                'item_gift_weight' => $this->item_gifts->item_gift_weight ?? 0,
                'fitem_gift_weight' => ($this->item_gifts->item_gift_weight == null) ? '0 Gram' : $this->item_gifts->item_gift_weight . ' Gram',
                'item_gift_status' => $this->item_gifts->item_gift_status,
                'item_gift_images' => $this->item_gifts->item_gift_images->map(function ($image) {
                    return [
                        'item_gift_id' => $image->item_gift_id,
                        'item_gift_image_url' => $image->item_gift_image_url,
                    ];
                }),
            ],
            'variants' => $this->variants->map(function ($variant) {
                return [
                    'id' => $variant->id,
                    'variant_name' => $variant->variant_name,
                    'variant_quantity' => $variant->variant_quantity,
                    'variant_point' => $variant->variant_point,
                    'fvariant_point' => format_money(strval($variant->variant_point)),
                ];
            }),
            'cart_quantity' => $this->cart_quantity,
            'users' => [
                'id' => $this->users->id,
                'name' => $this->users->name,
                'roles' => $this->users->getRoleNames(),
                'username' => $this->users->username,
                'email' => $this->users->email,
                'birthdate' => $this->users->birthdate,
                'address' => $this->users->address->map(function ($address) {
                    return [
                        'province' => [
                            'id' => $address->province->province_id,
                            'province_name' => $address->province->province_name
                        ],
                        'city' => [
                            'id' => $address->city->city_id,
                            'city_name' => $address->city->city_name
                        ],
                        'subdistrict' => [
                            'id' => $address->subdistrict->subdistrict_id,
                            'subdistrict_name' => $address->subdistrict->subdistrict_name
                        ],
                        'postal_code' => $address->postal_code,
                        'is_main' => $address->is_main,
                    ];
                }),
            ]
        ];
    }

    private function format_item_gift_point($item)
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
