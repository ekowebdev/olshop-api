<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ItemGiftImageResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'products' => [
                'id' => $this->item_gifts->id,
                'product_code' => $this->item_gifts->item_gift_code,
                'product_name' => $this->item_gifts->item_gift_name,
                'product_slug' => $this->item_gifts->item_gift_slug,
                'category' => ($this->item_gifts->category_id != null) ? $this->item_gifts->category->makeHidden(['created_at', 'updated_at']) : null,
                'brand' => ($this->item_gifts->brand_id != null) ? $this->item_gifts->brand->makeHidden(['created_at', 'updated_at']) : null,
                'product_description' => $this->item_gifts->item_gift_description,
                'product_spesification' => json_decode($this->item_gifts->item_gift_spesification) ?? [],
                'product_point' => $this->item_gifts->item_gift_point ?? 0,
                'fproduct_point' => $this->format_product_point($this->item_gifts),
                'product_weight' => $this->item_gifts->item_gift_weight ?? 0,
                'fproduct_weight' => $this->format_product_weight($this->item_gifts),
                'product_quantity' => $this->item_gifts->item_gift_quantity ?? 0,
                'product_status' => $this->item_gifts->item_gift_status,
            ],
            'variants' => ($this->variants) ? [
                'id' => $this->variants->id,
                'variant_name' => $this->variants->variant_name,
                'variant_slug' => $this->variants->variant_slug,
                'variant_quantity' => $this->variants->variant_quantity,
                'variant_point' => $this->variants->variant_point,
                'fvariant_point' => format_money(strval($this->variants->variant_point)),
                'variant_weight' => $this->variants->variant_weight,
                'fvariant_weight' => $this->variants->variant_weight . ' Gram',
            ] : null,
            'product_image' => $this->item_gift_image,
            'product_image_url' => $this->item_gift_image_url,
            'product_image_thumbnail_url' => $this->item_gift_image_thumb_url,
        ];
    }

    private function format_product_weight($item)
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

    private function format_product_point($item)
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
