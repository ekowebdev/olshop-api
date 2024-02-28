<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class WishlistResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'products' => [
                'id' => $this->item_gifts->id,
                'product_code' => $this->item_gifts->item_gift_code,
                'product_name' => $this->item_gifts->item_gift_name,
                'product_slug' => $this->item_gifts->item_gift_slug,
                'category' => ($this->item_gifts->category_id != null) ? $this->item_gifts->category->makeHidden(['created_at', 'updated_at']) : null,
                'brand' => ($this->item_gifts->brand_id != null) ? $this->item_gifts->brand->makeHidden(['created_at', 'updated_at']) : null,
                'product_description' => $this->item_gifts->item_gift_description,
                'product_spesification' => json_decode($this->item_gifts->item_gift_spesification) ?? [],
                'product_point' => $this->item_gifts->item_gift_point ?? 0,
                'fproduct_point' => $this->format_product_point($this->item_gifts),
                'product_weight' => $this->item_gifts->item_gift_weight ?? 0,
                'fproduct_weight' => $this->item_gifts->item_gift_weight . ' Gram',
                'product_quantity' => $this->item_gifts->item_gift_quantity ?? 0,
                'product_status' => $this->item_gifts->item_gift_status,
                'product_images' => $this->item_gifts->item_gift_images->map(function ($image) {
                    return [
                        'product_id' => $image->item_gift_id,
                        'variant_id' => $image->variant_id,
                        'product_image_url' => $image->item_gift_image_url,
                        'product_image_thumbnail_url' => $image->item_gift_image_thumb_url,
                    ];
                }),
                'variants' => $this->item_gifts->variants->map(function ($variant) {
                    return [
                        'id' => $variant->id,
                        'variant_name' => $variant->variant_name,
                        'variant_slug' => $variant->variant_slug,
                        'variant_quantity' => $variant->variant_quantity,
                        'variant_point' => $variant->variant_point,
                        'fvariant_point' => format_money(strval($variant->variant_point)),
                        'variant_weight' => $variant->variant_weight,
                        'fvariant_weight' => $variant->variant_weight . ' Gram',
                        'variant_image' => ($variant->item_gift_images) ? [
                            'id' => $variant->item_gift_images->id,
                            'image' => $variant->item_gift_images->item_gift_image,
                            'image_url' => $variant->item_gift_images->item_gift_image_url,
                            'image_thumb_url' => $variant->item_gift_images->item_gift_image_thumb_url,
                        ] : null,
                    ];
                }),
                'reviews' => $this->item_gifts->reviews->map(function ($review) {
                    return [
                        'id' => $review->id,
                        'users' => [
                            'id' => $review->users->id,
                            'name' => $review->users->profile->name,
                            'username' => $review->users->username,
                            'google_id' => $review->users->google_id,
                            'email' => $review->users->email,
                            'email_status' => $review->users->email_verified_at != null ? 'verified' : 'unverified',
                            'email_verified_at' => $review->users->email_verified_at,
                            'avatar_url' => ($review->users->profile) ? $review->users->profile->avatar_url : null,
                        ],
                        'redeem_id' => $review->redeem_id,
                        'product_id' => $review->item_gift_id,
                        'review_text' => $review->review_text,
                        'review_rating' => (float) $review->review_rating,
                        'review_files' => $review->review_files->makeHidden(['created_at', 'updated_at']),
                        'review_date' => $review->review_date,
                        'freview_date' => Carbon::parse($review->created_at)->diffForHumans(),
                    ];
                }),
                'total_review' => $this->item_gifts->total_reviews,
                'total_rating' => floatval(rtrim($this->item_gifts->total_rating, '0')),
                'total_order' => (int) $this->item_gifts->total_redeem,
                'is_wishlist' => $this->item_gifts->is_wishlist
            ],
            'users' => ($this->users) ? [
                'id' => $this->users->id,
                'username' => $this->users->username,
                'google_id' => $this->users->google_id,
                'email' => $this->users->email,
                'email_status' => $this->users->email_verified_at != null ? 'verified' : 'unverified',
                'email_verified_at' => $this->users->email_verified_at,
                'profile' => ($this->users->profile) ? [
                    'id' => $this->users->profile->id,
                    'name' => $this->users->profile->name,
                    'birthdate' => $this->users->profile->birthdate,
                    'phone_number' => $this->users->profile->phone_number,
                    'avatar' => $this->users->profile->avatar,
                    'avatar_url' => $this->users->profile->avatar_url,
                ] : null,
            ] : null
        ];
    }

    private function format_product_point($item)
    {
        if(count($item->variants) == 0){
            return format_money(strval($item->item_gift_point ?? 0));
        } else {
            $variant_points = $item->variants->pluck('variant_point')->toArray();
            if (count($variant_points) > 1) {
                $min_value = min($variant_points);
                $max_value = max($variant_points);
    
                if ($min_value === $max_value) {
                    return strval($min_value);
                }
    
                return format_money($min_value) . " ~ " . format_money($max_value);
            } else {
                return strval($variant_points[0]);
            }
        }
    }
}