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
            'text' => $this->text,
            'rating' => (float) $this->rating,
            'date' => $this->date,
            'has_files' => $this->has_files,
            'review_files' => $this->review_files->makeHidden(['created_at', 'updated_at']),
            'fdate' => Carbon::parse($this->created_at)->diffForHumans(),
            'orders' => ($this->orders) ? [
                'id' => $this->orders->id,
                'code' => $this->orders->code,
                'total_point' => $this->orders->total_point,
                'date' => $this->orders->date,
                'date' => Carbon::parse($this->orders->created_at)->format('Y-m-d H:i:s'),
                'fdate' => Carbon::parse($this->orders->created_at)->diffForHumans(),
                'note' => $this->orders->note,
                'snap_token' => $this->orders->snap_token,
                'snap_url' => $this->orders->snap_url,
                'metadata' => json_decode($this->orders->metadata),
                'status' => $this->orders->status,
                'order_products' => $this->orders->order_products->map(function ($order_product){
                    return [
                        'order_id' => $order_product->order_id,
                        'quantity' => $order_product->quantity,
                        'point' => $order_product->point,
                        'products' => [
                            'id' => $order_product->products->id,
                            'code' => $order_product->products->code,
                            'name' => $order_product->products->name,
                            'category' => ($order_product->products->category_id != null) ? $order_product->products->categories->makeHidden(['created_at', 'updated_at']) : null,
                            'brand' => ($order_product->products->brand_id != null) ? $order_product->products->brands->makeHidden(['created_at', 'updated_at']) : null,
                            'description' => $order_product->products->description,
                            'spesification' => json_decode($order_product->products->spesification) ?? [],
                            'point' => $order_product->products->point ?? 0,
                            'fpoint' => format_product_point($order_product->products),
                            'weight' => $order_product->products->weight ?? 0,
                            'fweight' => format_product_weight($order_product->products),
                            'status' => $order_product->products->status,
                            'product_images' => $order_product->products->product_images->map(function ($image) {
                                return [
                                    'product_id' => $image->id,
                                    'variant_id' => $image->variant_id,
                                    'image_url' => $image->image_url,
                                    'image_thumbnail_url' => $image->image_thumbnail_url,
                                ];
                            }),
                        ],
                        'variants' => ($order_product->variants)
                            ? [
                                'id' => $order_product->variants->id,
                                'name' => $order_product->variants->name,
                                'slug' => $order_product->variants->slug,
                                'quantity' => $order_product->variants->quantity,
                                'point' => $order_product->variants->point,
                                'fpoint' => format_money((string) $order_product->variants->point),
                                'variant_images' => ($order_product->variants->product_images) ? [
                                    'id' => $order_product->variants->product_images->id,
                                    'image' => $order_product->variants->product_images->image,
                                    'image_url' => $order_product->variants->product_images->image_url,
                                    'image_thumbnail_url' => $order_product->variants->product_images->image_thumbnail_url,
                                ] : null,
                            ] : null,
                    ];
                })
            ] : null,
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
                'fpoint' => format_product_point($this->products),
                'weight' => $this->products->weight ?? 0,
                'fweight' => format_product_weight($this->products),
                'quantity' => $this->products->quantity ?? 0,
                'status' => $this->products->status,
                'product_images' => $this->products->product_images->map(function ($image) {
                    return [
                        'product_id' => $image->product_id,
                        'variant_id' => $image->variant_id,
                        'image_url' => $image->image_url,
                        'image_thumbnail_url' => $image->image_thumbnail_url,
                    ];
                }),
                'variants' => $this->products->variants->map(function ($variant) {
                    return [
                        'id' => $variant->id,
                        'name' => $variant->name,
                        'slug' => $variant->slug,
                        'quantity' => $variant->quantity,
                        'point' => $variant->point,
                        'fpoint' => format_money((string) $variant->point),
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
            ],
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
            ] : null
        ];
    }
}
