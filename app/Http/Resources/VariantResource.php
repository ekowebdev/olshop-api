<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class VariantResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'variant_name' => $this->variant_name,
            'variant_slug' => $this->variant_slug,
            'item_gifts' => [
                'id' => $this->item_gifts->id,
                'item_gift_code' => $this->item_gifts->item_gift_code,
                'item_gift_name' => $this->item_gifts->item_gift_name,
                'item_gift_slug' => $this->item_gifts->item_gift_slug,
                'category' => ($this->item_gifts->category_id != null) ? $this->item_gifts->category->makeHidden(['created_at', 'updated_at']) : null,
                'brand' => ($this->item_gifts->brand_id != null) ? $this->item_gifts->brand->makeHidden(['created_at', 'updated_at']) : null,
                'item_gift_description' => $this->item_gifts->item_gift_description,
                'item_gift_spesification' => json_decode($this->item_gifts->item_gift_spesification) ?? [],
                'item_gift_point' => $this->item_gifts->item_gift_point ?? 0,
                'fitem_gift_point' => $this->format_item_gift_point($this->item_gifts),
                'item_gift_weight' => $this->item_gifts->item_gift_weight ?? 0,
                'fitem_gift_weight' => $this->format_item_gift_weight($this->item_gifts),
                'item_gift_quantity' => $this->item_gifts->item_gift_quantity ?? 0,
                'item_gift_status' => $this->item_gifts->item_gift_status,
                'item_gift_images' => $this->item_gifts->item_gift_images->map(function ($image) {
                    return [
                        'item_gift_id' => $image->item_gift_id,
                        'variant_id' => $image->variant_id,
                        'item_gift_image_url' => $image->item_gift_image_url,
                        'item_gift_image_thumbnail_url' => $image->item_gift_image_thumb_url,
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
                            'email' => $review->users->email,
                            'email_verified_at' => $review->users->email_verified_at,
                            'avatar_url' => ($review->users->profile) ? $review->users->profile->avatar_url : null,
                        ],
                        'redeem_id' => $review->redeem_id,
                        'item_gift_id' => $review->item_gift_id,
                        'review_text' => $review->review_text,
                        'review_rating' => (float) $review->review_rating,
                        'review_date' => $review->review_date,
                        'freview_date' => Carbon::parse($review->created_at)->diffForHumans(),
                    ];
                }),
                'total_reviews' => $this->item_gifts->total_reviews,
                'total_rating' => floatval(rtrim($this->item_gifts->total_rating, '0')),
                'total_redeem' => (int) $this->item_gifts->total_redeem,
                'is_wishlist' => $this->item_gifts->is_wishlist
            ],
            'variant_quantity' => $this->variant_quantity,
            'variant_point' => $this->variant_point,
            'fvariant_point' => format_money(strval($this->variant_point)),
            'variant_weight' => $this->variant_weight,
            'fvariant_weight' => $this->variant_weight . ' Gram',
            'variant_image' => ($this->item_gift_images) ? [
                'id' => $this->item_gift_images->id,
                'image' => $this->item_gift_images->item_gift_image,
                'image_url' => $this->item_gift_images->item_gift_image_url,
                'image_thumb_url' => $this->item_gift_images->item_gift_image_thumb_url,
            ] : null,
        ];
    }

    private function format_item_gift_weight($item)
    {
        if(count($item->variants) == 0){
            return strval($item->item_gift_weight ?? 0) . ' Gram';
        } else {
            $variant_weight = $item->variants->pluck('variant_weight')->toArray();
            if (count($variant_weight) > 1) {
                $variant_weight = min($variant_weight);
                return strval($variant_weight) . ' Gram';
            } else {
                return strval($variant_weight[0]) . ' Gram';
            }
        }
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
