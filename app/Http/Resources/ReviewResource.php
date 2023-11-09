<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'review_text' => $this->review_text,
            'review_rating' => (float) $this->review_rating,
            'review_date' => $this->review_date,
            'freview_date' => Carbon::parse($this->created_at)->diffForHumans(),
            'redeems' => ($this->redeems) ? [
                'redeem_id' => $this->redeems->id,
                'redeem_code' => $this->redeems->redeem_code,
                'total_point' => $this->redeems->total_point,
                'redeem_date' => $this->redeems->redeem_date,
                'redeem_date' => Carbon::parse($this->redeems->created_at)->format('Y-m-d H:i:s'),
                'fredeem_date' => Carbon::parse($this->redeems->created_at)->diffForHumans(),
                'note' => $this->redeems->note,
                'snap_url' => $this->redeems->snap_url,
                'metadata' => json_decode($this->redeems->metadata),
                'redeem_status' => $this->redeems->redeem_status,
                'redeem_item_gifts' => $this->redeems->redeem_item_gifts->map(function ($redeem_item_gift){
                    return [
                        'redeem_id' => $redeem_item_gift->redeem_id,
                        'redeem_quantity' => $redeem_item_gift->redeem_quantity,
                        'redeem_point' => $redeem_item_gift->redeem_point,
                        'item_gifts' => [
                            'id' => $redeem_item_gift->item_gifts->id,
                            'item_gift_code' => $redeem_item_gift->item_gifts->item_gift_code,
                            'item_gift_name' => $redeem_item_gift->item_gifts->item_gift_name,
                            'category' => ($redeem_item_gift->item_gifts->category_id != null) ? $redeem_item_gift->item_gifts->category->makeHidden(['created_at', 'updated_at']) : null,
                            'brand' => ($redeem_item_gift->item_gifts->brand_id != null) ? $redeem_item_gift->item_gifts->brand->makeHidden(['created_at', 'updated_at']) : null,
                            'item_gift_description' => $redeem_item_gift->item_gifts->item_gift_description,
                            'item_gift_spesification' => json_decode($redeem_item_gift->item_gifts->item_gift_spesification) ?? [],
                            'item_gift_point' => $redeem_item_gift->item_gifts->item_gift_point ?? 0,
                            'fitem_gift_point' => $this->format_item_gift_point($redeem_item_gift),
                            'item_gift_weight' => $redeem_item_gift->item_gifts->item_gift_weight ?? 0,
                            'fitem_gift_weight' => $this->format_item_gift_weight($redeem_item_gift),
                            'item_gift_status' => $redeem_item_gift->item_gifts->item_gift_status,
                            'item_gift_images' => $redeem_item_gift->item_gifts->item_gift_images->map(function ($image) {
                                return [
                                    'item_gift_id' => $image->item_gift_id,
                                    'variant_id' => $image->variant_id,
                                    'item_gift_image_url' => $image->item_gift_image_url,
                                    'item_gift_image_thumbnail_url' => $image->item_gift_image_thumb_url,
                                ];
                            }),
                        ],
                        'variants' => ($redeem_item_gift->variants) 
                            ? [
                                'id' => $redeem_item_gift->variants->id,
                                'variant_name' => $redeem_item_gift->variants->variant_name,
                                'variant_slug' => $redeem_item_gift->variants->variant_slug,
                                'variant_quantity' => $redeem_item_gift->variants->variant_quantity,
                                'variant_point' => $redeem_item_gift->variants->variant_point,
                                'fvariant_point' => format_money(strval($redeem_item_gift->variants->variant_point)),
                            ] : null,
                    ];
                })
            ] : null,
            'item_gifts' => [
                'id' => $this->item_gifts->id,
                'item_gift_code' => $this->item_gifts->item_gift_code,
                'item_gift_name' => $this->item_gifts->item_gift_name,
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
                    ];
                }),
            ],
            'users' => [
                'id' => $this->users->id,
                'name' => $this->users->name,
                'username' => $this->users->username,
                'email' => $this->users->email,
                'email_verified_at' => $this->users->email_verified_at,
                'profile' => ($this->users->profile) ? [
                    'id' => $this->users->profile->id,
                    'birthdate' => $this->users->profile->birthdate,
                    'phone_number' => $this->users->profile->phone_number,
                    'avatar' => $this->users->profile->avatar,
                    'avatar_url' => $this->users->profile->avatar_url,
                ] : null,
            ]
        ];
    }

    private function format_item_gift_weight($item)
    {
        $variant_weight = $item->variants->pluck('variant_weight')->toArray();
        if (count($variant_weight) == 1) {
            return strval($variant_weight[0]) . ' Gram';
        } elseif (count($variant_weight) > 1) {
            $variant_weight = min($variant_weight);
            return strval($variant_weight) . ' Gram';
        } else {
            return strval($this->item_gift_weight ?? 0) . ' Gram';
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
