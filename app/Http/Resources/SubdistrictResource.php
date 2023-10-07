<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SubdistrictResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'subdistrict_id' => $this->subdistrict_id,
            'city_id' => $this->city_id,
            'subdistrict_name' => $this->subdistrict_name
        ];
    }
}
