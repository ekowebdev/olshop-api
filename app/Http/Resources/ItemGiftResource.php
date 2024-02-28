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
            'product_code' => $this->item_gift_code,
            'product_name' => $this->item_gift_name,
            'product_slug' => $this->item_gift_slug,
            'category' => ($this->category_id != null) ? $this->category->makeHidden(['created_at', 'updated_at']) : null,
            'brand' => ($this->brand_id != null) ? $this->brand->makeHidden(['created_at', 'updated_at']) : null,
            'product_description' => $this->item_gift_description,
            'product_spesification' => json_decode($this->item_gift_spesification) ?? [],
            'product_point' => $this->item_gift_point ?? 0,
            'fproduct_point' => $this->format_product_point(),
            'product_weight' => $this->item_gift_weight ?? 0,
            'fproduct_weight' => $this->format_product_weight(),
            'product_quantity' => $this->item_gift_quantity ?? 0,
            'product_status' => $this->item_gift_status,
            'product_images' => $this->item_gift_images->makeHidden(['created_at', 'updated_at']),
            'variants' => $this->variants->map(function ($variant) {
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
            'reviews' => $this->reviews->map(function ($review) {
                return [
                    'id' => $review->id,
                    'users' => ($review->users) ? [
                        'id' => $review->users->id,
                        'name' => $review->users->profile->name,
                        'username' => $review->users->username,
                        'google_id' => $review->users->google_id,
                        'email' => $review->users->email,
                        'email_status' => $review->users->email_verified_at != null ? 'verified' : 'unverified',
                        'email_verified_at' => $review->users->email_verified_at,
                        'avatar_url' => ($review->users->profile) ? $review->users->profile->avatar_url : null,
                    ] : null,
                    'redeem_id' => $review->redeem_id,
                    'product_id' => $review->item_gift_id,
                    'review_text' => $review->review_text,
                    'review_rating' => (float) $review->review_rating,
                    'review_files' => $review->review_files->makeHidden(['created_at', 'updated_at']),
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

    private function format_product_weight()
    {
        $variant_weight = $this->variants->pluck('variant_weight')->toArray();
        if (count($variant_weight) == 1) {
            return strval($variant_weight[0]) . ' Gram';
        } elseif (count($variant_weight) > 1) {
            $variant_weight = min($variant_weight);
            return strval($variant_weight) . ' Gram';
        } else {
            return strval($this->item_gift_weight ?? 0) . ' Gram';
        }
    }

    private function format_product_point()
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
