<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use App\Http\Models\Review;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'order_products' => $this->order_products->map(function ($order_product) {
                return [
                    'order_id' => $order_product->order_id,
                    'quantity' => $order_product->quantity,
                    'point' => $order_product->point,
                    'fpoint' => formatMoney((string) $order_product->point ?? 0),
                    'products' => [
                        'id' => $order_product->products->id,
                        'code' => $order_product->products->code,
                        'name' => $order_product->products->name,
                        'slug' => $order_product->products->slug,
                        'category' => ($order_product->products->category_id != null) ? $order_product->products->categories->makeHidden(['created_at', 'updated_at']) : null,
                        'brand' => ($order_product->products->brand_id != null) ? $order_product->products->brands->makeHidden(['created_at', 'updated_at']) : null,
                        'description' => $order_product->products->description,
                        'spesification' => json_decode($order_product->products->spesification) ?? [],
                        'point' => $order_product->products->point ?? 0,
                        'fpoint' => formatProductPoint($order_product->products),
                        'weight' => $order_product->products->weight ?? 0,
                        'fweight' => formatProductWeight($order_product->products),
                        'status' => $order_product->products->status,
                        'main_image' => $order_product->products->main_image_url,
                        'product_images' => $order_product->products->product_images->map(function ($image) {
                            return [
                                'product_id' => $image->product_id,
                                'variant_id' => $image->variant_id,
                                'image_url' => $image->image_url,
                                'image_thumbnail_url' => $image->image_thumbnail_url,
                                'is_primary' => $image->is_primary,
                            ];
                        }),
                        'reviews' => $order_product->products->reviews->map(function ($review) {
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
                                'review_files' => $review->review_files->makeHidden(['created_at', 'updated_at']),
                                'date' => $review->date,
                                'fdate' => Carbon::parse($review->created_at)->diffForHumans(),
                            ];
                        }),
                        'total_review' => $order_product->products->total_review,
                        'total_rating' => (float) rtrim($order_product->products->total_rating, '0'),
                        'total_order' => (int) $order_product->products->total_order,
                        'is_reviewed' => isReviewed($order_product->products->id, $this->id)
                    ],
                    'variants' => ($order_product->variants)
                        ? [
                            'id' => $order_product->variants->id,
                            'name' => $order_product->variants->name,
                            'slug' => $order_product->variants->slug,
                            'quantity' => $order_product->variants->quantity,
                            'point' => $order_product->variants->point,
                            'fpoint' => formatMoney((string) $order_product->variants->point),
                            'weight' => $order_product->variants->weight,
                            'fweight' => $order_product->variants->weight . ' Gram',
                            'variant_images' => ($order_product->variants->product_images) ? [
                                'id' => $order_product->variants->product_images->id,
                                'image' => $order_product->variants->product_images->image,
                                'image_url' => $order_product->variants->product_images->image_url,
                                'image_thumbnail_url' => $order_product->variants->product_images->image_thumbnail_url,
                                'is_primary' => $order_product->variants->product_images->is_primary,
                            ] : null,
                        ] : null,
                ];
            }),
            'total_point' => $this->total_point,
            'ftotal_point' => formatMoney((string) $this->total_point ?? 0),
            'shipping_fee' => $this->shipping_fee,
            'fshipping_fee' => formatMoney((string) $this->shipping_fee ?? 0),
            'total_amount' => $this->total_amount,
            'ftotal_amount' => formatMoney((string) $this->total_amount ?? 0),
            'note' => $this->note,
            'date' => Carbon::parse($this->created_at)->format('Y-m-d H:i:s'),
            'fdate' => Carbon::parse($this->created_at)->diffForHumans(),
            'snap_token' => $this->snap_token,
            'snap_url' => $this->snap_url,
            'metadata' => json_decode($this->metadata),
            'status' => $this->status,
            'payments' => ($this->payment_logs) ? [
                'id' => $this->payment_logs->id,
                'type' => $this->payment_logs->type,
                'raw_response' => json_decode($this->payment_logs->raw_response),
                'status' => $this->payment_logs->status,
            ] : null,
            'shippings' => [
                'id' => $this->shippings->id,
                'origin' => [
                    'id' => $this->shippings->city_origin->id,
                    'city' => $this->shippings->city_origin->name
                ],
                'destination' => [
                    'id' => $this->shippings->city_destination->id,
                    'city' => $this->shippings->city_destination->name
                ],
                'weight' => $this->shippings->weight,
                'fweight' => $this->shippings->weight . ' Gram',
                'courier' => $this->shippings->courier,
                'service' => $this->shippings->service,
                'description' => $this->shippings->description,
                'cost' => $this->shippings->cost,
                'etd' => $this->shippings->etd,
                'resi' => $this->shippings->resi,
                'status' => $this->shippings->status,
            ],
            'users' => ($this->users) ? [
                'id' => $this->users->id,
                'roles' => $this->users->getRoleNames(),
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
                'address' => ($this->address) ? [
                    'id' => $this->address->id,
                    'person_name' => $this->address->person_name,
                    'person_phone' => $this->address->person_phone,
                    'province' => [
                        'id' => $this->address->province->id,
                        'name' => $this->address->province->name
                    ],
                    'city' => [
                        'id' => $this->address->city->id,
                        'name' => $this->address->city->name
                    ],
                    'subdistrict' => [
                        'id' => $this->address->subdistrict->id,
                        'name' => $this->address->subdistrict->name
                    ],
                    'postal_code' => $this->address->postal_code,
                    'street' => $this->address->street,
                    'is_main' => ($this->users->main_address_id == $this->address->id) ? 1 : 0,
                ] : null,
            ] : null
        ];
    }
}
