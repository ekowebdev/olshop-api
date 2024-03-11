<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductImageResource extends JsonResource
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
                'quantity' => $this->products->quantity ?? 0,
                'status' => $this->products->status,
            ],
            'variants' => ($this->variants) ? [
                'id' => $this->variants->id,
                'name' => $this->variants->name,
                'slug' => $this->variants->slug,
                'quantity' => $this->variants->quantity,
                'point' => $this->variants->point,
                'fpoint' => format_money(strval($this->variants->point)),
                'weight' => $this->variants->weight,
                'fweight' => $this->variants->weight . ' Gram',
            ] : null,
            'image' => $this->image,
            'image_url' => $this->image_url,
            'image_thumbnail_url' => $this->image_thumbnail_url,
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
