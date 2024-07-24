<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class ShippingResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
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
                        ],
                        'variants' => ($order_product->variants) ? [
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
                })
            ],
            'origin' => [
                'id' => $this->city_origin->id,
                'city_name' => $this->city_origin->name
            ],
            'destination' => [
                'id' => $this->city_destination->id,
                'city_name' => $this->city_destination->name
            ],
            'weight' => $this->weight,
            'fweight' => $this->weight . ' Gram',
            'courier' => $this->courier,
            'service' => $this->service,
            'description' => $this->description,
            'cost' => $this->cost,
            'etd' => $this->etd,
            'resi' => $this->resi,
            'status' => $this->status,
        ];
    }
}
