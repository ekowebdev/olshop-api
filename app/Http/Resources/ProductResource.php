<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use App\Http\Models\Product;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'slug' => $this->slug,
            'category' => ($this->category_id != null) ? $this->categories->makeHidden(['created_at', 'updated_at']) : null,
            'brand' => ($this->brand_id != null) ? $this->brands->makeHidden(['created_at', 'updated_at']) : null,
            'description' => $this->description,
            'spesification' => json_decode($this->spesification) ?? [],
            'point' => $this->point ?? 0,
            'fpoint' => $this->format_product_point(),
            'weight' => $this->weight ?? 0,
            'fweight' => $this->format_product_weight(),
            'quantity' => $this->quantity ?? 0,
            'status' => $this->status,
            'product_images' => $this->product_images->makeHidden(['created_at', 'updated_at']),
            'variants' => $this->variants->map(function ($variant) {
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
                        'image_thumbnail_url' => $variant->product_images->image_thumbnail_url,
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
                        'has_password' => $review->users->has_password,
                        'avatar_url' => ($review->users->profile) ? $review->users->profile->avatar_url : null,
                    ] : null,
                    'order_id' => $review->order_id,
                    'product_id' => $review->product_id,
                    'text' => $review->text,
                    'rating' => (float) $review->rating,
                    'review_files' => $review->review_files->makeHidden(['created_at', 'updated_at']),
                    'date' => $review->date,
                    'fdate' => Carbon::parse($review->created_at)->diffForHumans(),
                ];
            }),
            'total_review' => $this->total_review,
            'total_rating' => floatval(rtrim($this->total_rating, '0')),
            'total_order' => (int) $this->total_order,
            'is_wishlist' => $this->is_wishlist
        ];
    }

    private function format_product_weight()
    {
        $weight = $this->variants->pluck('weight')->toArray();
        if (count($weight) == 1) {
            return strval($weight[0]) . ' Gram';
        } elseif (count($weight) > 1) {
            $weight = min($weight);
            return strval($weight) . ' Gram';
        } else {
            return strval($this->weight ?? 0) . ' Gram';
        }
    }

    private function format_product_point()
    {
        $points = $this->variants->pluck('point')->toArray();
        if (count($points) == 1) {
            return format_money(strval($points[0]));
        } elseif (count($points) > 1) {
            $min_value = min($points);
            $max_value = max($points);
            if ($min_value === $max_value) {
                return format_money(strval($min_value));
            }
            return format_money($min_value) . " ~ " . format_money($max_value);
        } else {
            return format_money(strval($this->point ?? 0));
        }
    }
}
