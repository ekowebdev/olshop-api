<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CityResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'city_id' => $this->city_id,
            'province' => [
                'province_id' => $this->province->province_id,
                'province_name' => $this->province->province_name,
            ],
            'city_name' => $this->city_name,
            'postal_code' => $this->postal_code
        ];
    }
}
