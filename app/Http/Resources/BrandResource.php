<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BrandResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'brand_name' => $this->brand_name,
            'brand_slug' => $this->brand_slug,
            'brand_logo' => $this->brand_logo,
            'brand_logo_url' => $this->brand_logo_url,
            'brand_sort' => $this->brand_sort,
        ];
    }
}
