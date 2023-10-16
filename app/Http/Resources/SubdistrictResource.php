<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SubdistrictResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'subdistrict_id' => $this->subdistrict_id,
            'city' => [
                'city_id' => $this->city->city_id,
                'city_name' => $this->city->city_name,
            ],
            'subdistrict_name' => $this->subdistrict_name
        ];
    }
}
