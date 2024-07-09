<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentLogResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'orders' => [
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
                            'slug' => $order_product->products->slug,
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
                                    'product_image_url' => $image->image_url,
                                    'product_image_thumbnail_url' => $image->image_thumbnail_url,
                                ];
                            }),
                        ],
                        'variants' => ($order_product->variants) ? [
                            'id' => $order_product->variants->id,
                            'name' => $order_product->variants->name,
                            'slug' => $order_product->variants->slug,
                            'quantity' => $order_product->variants->quantity,
                            'point' => $order_product->variants->point,
                            'fpoint' => format_money(strval($order_product->variants->point)),
                            'weight' => $order_product->variants->weight,
                            'fweight' => $order_product->variants->weight . ' Gram',
                            'variant_images' => ($order_product->variants->product_images) ? [
                                'id' => $order_product->variants->product_images->id,
                                'image' => $order_product->variants->product_images->image,
                                'image_url' => $order_product->variants->product_images->image_url,
                                'image_thumbnail_url' => $order_product->variants->product_images->image_thumbnail_url,
                            ] : null,
                        ] : null,
                    ];
                })
            ],
            'raw_response' => json_decode($this->raw_response),
            'status' => $this->status,
        ];
    }
}
