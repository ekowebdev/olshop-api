<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CityResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->city_id,
            'province' => [
                'id' => $this->province->province_id,
                'name' => $this->province->name,
            ],
            'name' => $this->name,
            'postal_code' => $this->postal_code
        ];
    }
}
