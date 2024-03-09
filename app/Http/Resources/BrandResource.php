<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BrandResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'logo' => $this->logo,
            'logo_url' => $this->logo_url,
            'logo_thumb_url' => $this->logo_thumb_url,
            'sort' => $this->sort,
        ];
    }
}
