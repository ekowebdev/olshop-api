<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProvinceResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'province_id' => $this->province_id,
            'province_name' => $this->province_name
        ];
    }
}
