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
                            'fpoint' => $this->format_product_point($order_product),
                            'weight' => $order_product->products->weight ?? 0,
                            'fweight' => $this->format_product_weight($order_product),
                            'status' => $order_product->products->status,
                            'product_images' => $order_product->products->product_images->map(function ($image) {
                                return [
                                    'product_id' => $image->product_id,
                                    'variant_id' => $image->variant_id,
                                    'image_url' => $image->image_url,
                                    'image_thumbnail_url' => $image->image_thumb_url,
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
                                'image_thumb_url' => $order_product->variants->product_images->image_thumb_url,
                            ] : null,
                        ] : null,
                    ];
                })
            ],
            'origin' => [
                'id' => $this->city_origin->city_id,
                'city_name' => $this->city_origin->city_name
            ],
            'destination' => [
                'id' => $this->city_destination->city_id,
                'city_name' => $this->city_destination->city_name
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

    private function format_product_weight($product)
    {
        $weight = $product->products->variants->pluck('weight')->toArray();
        if (count($weight) == 1) {
            return strval($weight[0]) . ' Gram';
        } elseif (count($weight) > 1) {
            $weight = min($weight);
            return strval($weight) . ' Gram';
        } else {
            return strval($product->products->weight ?? 0) . ' Gram';
        }
    }

    private function format_product_point($product)
    {
        $points = $product->products->variants->pluck('point')->toArray();
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
            return format_money(strval($product->products->point ?? 0));
        }
    }
}
