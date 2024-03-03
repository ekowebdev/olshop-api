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
            'name' => $this->name,
            'slug' => $this->slug,
            'products' => [
                'id' => $this->products->id,
                'code' => $this->products->code,
                'name' => $this->products->name,
                'slug' => $this->products->slug,
                'category' => ($this->products->category_id != null) ? $this->products->categories->makeHidden(['created_at', 'updated_at']) : null,
                'brand' => ($this->products->brand_id != null) ? $this->products->brands->makeHidden(['created_at', 'updated_at']) : null,
                'description' => $this->products->description,
                'spesification' => json_decode($this->products->spesification) ?? [],
                'point' => $this->products->point ?? 0,
                'fpoint' => $this->format_product_point($this->products),
                'weight' => $this->products->weight ?? 0,
                'fweight' => $this->format_product_weight($this->products),
                'quantity' => $this->products->quantity ?? 0,
                'status' => $this->products->status,
                'product_images' => $this->products->product_images->map(function ($image) {
                    return [
                        'product_id' => $image->product_id,
                        'variant_id' => $image->variant_id,
                        'image_url' => $image->image_url,
                        'image_thumbnail_url' => $image->image_thumb_url,
                    ];
                }),
                'variants' => $this->products->variants->map(function ($variant) {
                    return [
                        'id' => $variant->id,
                        'name' => $variant->name,
                        'slug' => $variant->slug,
                        'quantity' => $variant->quantity,
                        'point' => $variant->point,
                        'fpoint' => format_money(strval($variant->point)),
                        'weight' => $variant->weight,
                        'fweight' => $variant->weight . ' Gram',
                        'variant_images' => ($variant->product_images) ? [
                            'id' => $variant->product_images->id,
                            'image' => $variant->product_images->image,
                            'image_url' => $variant->product_images->image_url,
                            'image_thumb_url' => $variant->product_images->image_thumb_url,
                        ] : null,
                    ];
                }),
                'reviews' => $this->products->reviews->map(function ($review) {
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
                        'order_id' => $review->order_id,
                        'product_id' => $review->product_id,
                        'text' => $review->text,
                        'rating' => (float) $review->rating,
                        'files' => $review->review_files->makeHidden(['created_at', 'updated_at']),
                        'date' => $review->date,
                        'fdate' => Carbon::parse($review->created_at)->diffForHumans(),
                    ];
                }),
                'total_review' => $this->products->total_review,
                'total_rating' => floatval(rtrim($this->products->total_rating, '0')),
                'total_order' => (int) $this->products->total_order,
                'is_wishlist' => $this->products->is_wishlist
            ],
            'quantity' => $this->quantity,
            'point' => $this->point,
            'fpoint' => format_money(strval($this->point)),
            'weight' => $this->weight,
            'fweight' => $this->weight . ' Gram',
            'variant_images' => ($this->product_images) ? [
                'id' => $this->product_images->id,
                'image' => $this->product_images->image,
                'image_url' => $this->product_images->image_url,
                'image_thumb_url' => $this->product_images->image_thumb_url,
            ] : null,
        ];
    }

    private function format_product_weight($product)
    {
        if(count($product->variants) == 0){
            return strval($product->weight ?? 0) . ' Gram';
        } else {
            $weight = $product->variants->pluck('weight')->toArray();
            if (count($weight) > 1) {
                $weight = min($weight);
                return strval($weight) . ' Gram';
            } else {
                return strval($weight[0]) . ' Gram';
            }
        }
    }

    private function format_product_point($product)
    {
        $points = $product->variants->pluck('point')->toArray();
        if (count($points) == 1) {
            return strval($points[0]);
        } elseif (count($points) > 1) {
            $min_value = min($points);
            $max_value = max($points);
            if ($min_value === $max_value) {
                return strval($min_value);
            }
            return format_money($min_value) . " ~ " . format_money($max_value);
        } else {
            return format_money(strval($this->point ?? 0));
        }
    }
}
