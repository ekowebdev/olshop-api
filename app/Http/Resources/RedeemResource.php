<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use App\Http\Models\Review;
use Illuminate\Http\Resources\Json\JsonResource;

class RedeemResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'redeem_code' => $this->redeem_code,
            'redeem_products' => $this->redeem_item_gifts->map(function ($redeem_item_gift) {
                return [
                    'redeem_id' => $redeem_item_gift->redeem_id,
                    'redeem_quantity' => $redeem_item_gift->redeem_quantity,
                    'redeem_point' => $redeem_item_gift->redeem_point,
                    'fredeem_point' => format_money(strval($redeem_item_gift->redeem_point ?? 0)),
                    'products' => [
                        'id' => $redeem_item_gift->item_gifts->id,
                        'product_code' => $redeem_item_gift->item_gifts->item_gift_code,
                        'product_name' => $redeem_item_gift->item_gifts->item_gift_name,
                        'product_slug' => $redeem_item_gift->item_gifts->item_gift_slug,
                        'category' => ($redeem_item_gift->item_gifts->category_id != null) ? $redeem_item_gift->item_gifts->category->makeHidden(['created_at', 'updated_at']) : null,
                        'brand' => ($redeem_item_gift->item_gifts->brand_id != null) ? $redeem_item_gift->item_gifts->brand->makeHidden(['created_at', 'updated_at']) : null,
                        'product_description' => $redeem_item_gift->item_gifts->item_gift_description,
                        'product_spesification' => json_decode($redeem_item_gift->item_gifts->item_gift_spesification) ?? [],
                        'product_point' => $redeem_item_gift->item_gifts->item_gift_point ?? 0,
                        'fproduct_point' => $this->format_product_point($redeem_item_gift),
                        'product_weight' => $redeem_item_gift->item_gifts->item_gift_weight ?? 0,
                        'fproduct_weight' => $this->format_product_weight($redeem_item_gift),
                        'product_status' => $redeem_item_gift->item_gifts->item_gift_status,
                        'product_images' => $redeem_item_gift->item_gifts->item_gift_images->map(function ($image) {
                            return [
                                'product_id' => $image->item_gift_id,
                                'variant_id' => $image->variant_id,
                                'product_image_url' => $image->item_gift_image_url,
                                'product_image_thumbnail_url' => $image->item_gift_image_thumb_url,
                            ];
                        }),
                        'reviews' => $redeem_item_gift->item_gifts->reviews->map(function ($review) {
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
                        'total_review' => $redeem_item_gift->item_gifts->total_reviews,
                        'total_rating' => floatval(rtrim($redeem_item_gift->item_gifts->total_rating, '0')),
                        'total_order' => (int) $redeem_item_gift->item_gifts->total_redeem,
                        'is_reviewed' => $this->is_reviewed($redeem_item_gift->item_gifts->id, $this->id)
                    ],
                    'variants' => ($redeem_item_gift->variants) 
                        ? [
                            'id' => $redeem_item_gift->variants->id,
                            'variant_name' => $redeem_item_gift->variants->variant_name,
                            'variant_slug' => $redeem_item_gift->variants->variant_slug,
                            'variant_quantity' => $redeem_item_gift->variants->variant_quantity,
                            'variant_point' => $redeem_item_gift->variants->variant_point,
                            'fvariant_point' => format_money(strval($redeem_item_gift->variants->variant_point)),
                            'variant_weight' => $redeem_item_gift->variants->variant_weight,
                            'fvariant_weight' => $redeem_item_gift->variants->variant_weight . ' Gram',
                            'variant_image' => ($redeem_item_gift->variants->item_gift_images) ? [
                                'id' => $redeem_item_gift->variants->item_gift_images->id,
                                'image' => $redeem_item_gift->variants->item_gift_images->item_gift_image,
                                'image_url' => $redeem_item_gift->variants->item_gift_images->item_gift_image_url,
                                'image_thumb_url' => $redeem_item_gift->variants->item_gift_images->item_gift_image_thumb_url,
                            ] : null,
                        ] : null,
                ];
            }),
            'total_point' => $this->total_point,
            'ftotal_point' => format_money(strval($this->total_point ?? 0)),
            'shipping_fee' => $this->shipping_fee,
            'fshipping_fee' => format_money(strval($this->shipping_fee ?? 0)),
            'total_amount' => $this->total_amount,
            'ftotal_amount' => format_money(strval($this->total_amount ?? 0)),
            'redeem_date' => $this->redeem_date,
            'note' => $this->note,
            'redeem_date' => Carbon::parse($this->created_at)->format('Y-m-d H:i:s'),
            'fredeem_date' => Carbon::parse($this->created_at)->diffForHumans(),
            'snap_token' => $this->snap_token,
            'snap_url' => $this->snap_url,
            'metadata' => json_decode($this->metadata),
            'redeem_status' => $this->redeem_status,
            'payments' => ($this->payment_logs) ? [
                'id' => $this->payment_logs->id,
                'payment_type' => $this->payment_logs->payment_type,
                'raw_response' => json_decode($this->payment_logs->raw_response),
                'payment_status' => $this->payment_logs->payment_status,
            ] : null,
            'shippings' => [
                'id' => $this->shippings->id,
                'shipping_origin' => [
                    'id' => $this->shippings->city_origin->city_id,
                    'city_name' => $this->shippings->city_origin->city_name
                ],
                'shipping_destination' => [
                    'id' => $this->shippings->city_destination->city_id,
                    'city_name' => $this->shippings->city_destination->city_name
                ],
                'shipping_weight' => $this->shippings->weight,
                'fshipping_weight' => $this->shippings->weight . ' Gram',
                'shipping_courier' => $this->shippings->courier,
                'shipping_service' => $this->shippings->service,
                'shipping_description' => $this->shippings->description,
                'shipping_cost' => $this->shippings->cost,
                'shipping_etd' => $this->shippings->etd,
                'shipping_resi' => $this->shippings->resi,
                'shipping_status' => $this->shippings->status,
            ],
            'users' => ($this->users) ? [
                'id' => $this->users->id,
                'roles' => $this->users->getRoleNames(),
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
                'address' => ($this->address) ? [
                    'id' => $this->address->id,
                    'person_name' => $this->address->person_name,
                    'person_phone' => $this->address->person_phone,
                    'province' => [
                        'id' => $this->address->province->province_id,
                        'province_name' => $this->address->province->province_name
                    ],
                    'city' => [
                        'id' => $this->address->city->city_id,
                        'city_name' => $this->address->city->city_name
                    ],
                    'subdistrict' => [
                        'id' => $this->address->subdistrict->subdistrict_id,
                        'subdistrict_name' => $this->address->subdistrict->subdistrict_name
                    ],
                    'postal_code' => $this->address->postal_code,
                    'address' => $this->address->address,
                    'is_main' => ($this->users->main_address_id == $this->address->id) ? 1 : 0,
                ] : null,
            ] : null
        ];
    }

    private function format_product_weight($item)
    {
        $variant_weight = $item->item_gifts->variants->pluck('variant_weight')->toArray();
        if (count($variant_weight) == 1) {
            return strval($variant_weight[0]) . ' Gram';
        } elseif (count($variant_weight) > 1) {
            $variant_weight = min($variant_weight);
            return strval($variant_weight) . ' Gram';
        } else {
            return strval($item->item_gifts->item_gift_weight ?? 0) . ' Gram';
        }
    }

    private function format_product_point($item)
    {
        $variant_points = $item->item_gifts->variants->pluck('variant_point')->toArray();
        
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
            return format_money(strval($item->item_gifts->item_gift_point ?? 0));
        }
    }

    private function is_reviewed($item_gift_id, $redeem_id)
    {
        $user_id = (auth()->user()) ? auth()->user()->id : 0;

        $reviews = Review::where('user_id', $user_id)
            ->where('product_id', $item_gift_id)
            ->where('redeem_id', $redeem_id)
            ->get();

        return (count($reviews) > 0) ? 1 : 0;
    }
}
