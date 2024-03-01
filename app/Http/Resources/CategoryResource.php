<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'slug' => $this->slug,
            'image' => $this->image,
            'image_url' => $this->image_url,
            'sort' => $this->sort,
            'status' => $this->status,
        ];
    }
}
