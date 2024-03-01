<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProvinceResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->province_id,
            'name' => $this->name
        ];
    }
}
