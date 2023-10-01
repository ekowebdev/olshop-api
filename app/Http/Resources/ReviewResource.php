<?php

namespace App\Http\Resources;

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
                'item_gift_weight' => $this->item_gifts->item_gift_weight ?? 0,
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
