<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'category_code' => $this->category_code,
            'category_name' => $this->category_name,
            'category_slug' => $this->category_slug,
            'category_image' => $this->category_image,
            'category_image_url' => $this->category_image_url,
            'category_sort' => $this->category_sort,
            'category_status' => $this->category_status,
        ];
    }
}
