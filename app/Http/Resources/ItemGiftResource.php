<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemGiftResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'item_gift_code' => $this->item_gift_code,
            'item_gift_name' => $this->item_gift_name,
            'category' => ($this->category_id != null) ? $this->category->makeHidden(['created_at', 'updated_at']) : null,
            'brand' => ($this->brand_id != null) ? $this->brand->makeHidden(['created_at', 'updated_at']) : null,
            'item_gift_slug' => $this->item_gift_slug,
            'item_gift_description' => $this->item_gift_description,
            'item_gift_spesification' => json_decode($this->item_gift_spesification),
            'item_gift_point' => $this->item_gift_point ?? 0,
            'fitem_gift_point' => $this->format_item_gift_point(),
            'item_gift_weight' => $this->item_gift_weight ?? 0,
            'fitem_gift_weight' => ($this->item_gift_weight == null) ? '0 Gram' : $this->item_gift_weight . ' Gram',
            'item_gift_quantity' => $this->item_gift_quantity ?? 0,
            'item_gift_status' => $this->item_gift_status,
            'item_gift_images' => $this->item_gift_images->makeHidden(['created_at', 'updated_at']),
            'variants' => $this->variants->map(function ($variant) {
                return [
                    'id' => $variant->id,
                    'variant_name' => $variant->variant_name,
                    'variant_quantity' => $variant->variant_quantity,
                    'variant_point' => $variant->variant_point,
                    'fvariant_point' => format_money(strval($variant->variant_point)),
                ];
            }),
            'reviews' => $this->reviews->map(function ($review) {
                return [
                    'id' => $review->id,
                    'users' => [
                        'id' => $review->users->id,
                        'name' => $review->users->name,
                        'roles' => $review->users->getRoleNames(),
                        'username' => $review->users->username,
                        'email' => $review->users->email,
                        'profile' => ($review->users->profile) ? [
                            'id' => $review->users->profile->id,
                            'birthdate' => $review->users->profile->birthdate,
                            'phone_number' => $review->users->profile->phone_number,
                            'avatar' => $review->users->profile->avatar,
                            'avatar_url' => $review->users->profile->avatar_url,
                        ] : null,
                        'main_address' => ($review->users->main_address) ? [
                            'id' => $review->users->main_address->id,
                            'person_name' => $review->users->main_address->person_name,
                            'person_phone' => $review->users->main_address->person_phone,
                            'province' => [
                                'id' => $review->users->main_address->province->province_id,
                                'province_name' => $review->users->main_address->province->province_name
                            ],
                            'city' => [
                                'id' => $review->users->main_address->city->city_id,
                                'city_name' => $review->users->main_address->city->city_name
                            ],
                            'subdistrict' => [
                                'id' => $review->users->main_address->subdistrict->subdistrict_id,
                                'subdistrict_name' => $review->users->main_address->subdistrict->subdistrict_name
                            ],
                            'postal_code' => $review->users->main_address->postal_code,
                            'address' => $review->users->main_address->address,
                        ] : null,
                        'address' => $review->users->address->map(function ($address) {
                            return [
                                'id' => $address->id,
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
                                'is_main' => ($address->users->main_address_id == $address->id) ? 1 : 0,
                            ];
                        }),
                    ],
                    'item_gift_id' => $review->item_gift_id,
                    'review_text' => $review->review_text,
                    'review_rating' => (float) $review->review_rating,
                    'review_date' => $review->review_date,
                    'freview_date' => Carbon::parse($review->created_at)->diffForHumans(),
                ];
            }),
            'total_reviews' => $this->total_reviews,
            'total_rating' => floatval(rtrim($this->total_rating, '0')),
            'total_redeem' => (int) $this->total_redeem,
            'is_wishlist' => $this->is_wishlist
        ];
    }

    private function format_item_gift_point()
    {
        $variant_points = $this->variants->pluck('variant_point')->toArray();
        
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
