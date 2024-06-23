<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
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
                'status' => $this->products->status,
                'product_images' => $this->products->product_images->map(function ($image) {
                    return [
                        'product_id' => $image->product_id,
                        'variant_id' => $image->variant_id,
                        'image_url' => $image->image_url,
                        'image_thumbnail_url' => $image->image_thumbnail_url,
                    ];
                }),
            ],
            'variants' => ($this->variants)
                ? [
                    'id' => $this->variants->id,
                    'name' => $this->variants->name,
                    'slug' => $this->variants->slug,
                    'quantity' => $this->variants->quantity,
                    'point' => $this->variants->point,
                    'fpoint' => format_money(strval($this->variants->point)),
                    'weight' => $this->variants->weight,
                    'fweight' => $this->variants->weight . ' Gram',
                    'variant_images' => ($this->variants->product_images) ? [
                        'id' => $this->variants->product_images->id,
                        'image' => $this->variants->product_images->image,
                        'image_url' => $this->variants->product_images->image_url,
                        'image_thumbnail_url' => $this->variants->product_images->image_thumbnail_url,
                    ] : null,
                ] : null,
            'quantity' => $this->quantity,
            'users' => ($this->users) ? [
                'id' => $this->users->id,
                'username' => $this->users->username,
                'google_id' => $this->users->google_id,
                'email' => $this->users->email,
                'email_status' => $this->users->email_verified_at != null ? 'verified' : 'unverified',
                'email_verified_at' => $this->users->email_verified_at,
                'has_password' => $this->users->has_password,
                'profile' => ($this->users->profile) ? [
                    'id' => $this->users->profile->id,
                    'name' => $this->users->profile->name,
                    'birthdate' => $this->users->profile->birthdate,
                    'phone_number' => $this->users->profile->phone_number,
                    'avatar' => $this->users->profile->avatar,
                    'avatar_url' => $this->users->profile->avatar_url,
                ] : null,
            ] : null,
            'created_at' => Carbon::parse($this->created_at)->format('Y-m-d H:i:s'),
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
        if(count($product->variants) == 0){
            return format_money(strval($product->point ?? 0));
        } else {
            $points = $product->variants->pluck('point')->toArray();
            if (count($points) > 1) {
                $min_value = min($points);
                $max_value = max($points);
                if ($min_value === $max_value) {
                    return strval($min_value);
                }
                return format_money($min_value) . " ~ " . format_money($max_value);
            } else {
                return strval($points[0]);
            }
        }
    }
}
