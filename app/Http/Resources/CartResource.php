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
                'item_gift_status' => $this->item_gifts->item_gift_status,
                'item_gift_images' => $this->item_gifts->item_gift_images->map(function ($image) {
                    return [
                        'item_gift_id' => $image->item_gift_id,
                        'variant_id' => $image->variant_id,
                        'item_gift_image_url' => $image->item_gift_image_url,
                        'item_gift_image_thumbnail_url' => $image->item_gift_image_thumb_url,
                    ];
                }),
            ],
            'variants' => ($this->variants) 
                ? [
                    'id' => $this->variants->id,
                    'variant_name' => $this->variants->variant_name,
                    'variant_slug' => $this->variants->variant_slug,
                    'variant_quantity' => $this->variants->variant_quantity,
                    'variant_point' => $this->variants->variant_point,
                    'fvariant_point' => format_money(strval($this->variants->variant_point)),
                    'variant_weight' => $this->variants->variant_weight,
                    'fvariant_weight' => $this->variants->variant_weight . ' Gram',
                    'variant_image' => ($this->variants->item_gift_images) ? [
                        'id' => $this->variants->item_gift_images->id,
                        'image' => $this->variants->item_gift_images->item_gift_image,
                        'image_url' => $this->variants->item_gift_images->item_gift_image_url,
                        'image_thumb_url' => $this->variants->item_gift_images->item_gift_image_thumb_url,
                    ] : null,
                ] : null,
            'cart_quantity' => $this->cart_quantity,
            'users' => ($this->users) ? [
                'id' => $this->users->id,
                'username' => $this->users->username,
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
            ] : null,
            'created_at' => Carbon::parse($this->created_at)->format('Y-m-d H:i:s'),
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
