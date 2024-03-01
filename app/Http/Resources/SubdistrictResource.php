<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SubdistrictResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->subdistrict_id,
            'city' => [
                'id' => $this->city->city_id,
                'name' => $this->city->name,
            ],
            'name' => $this->name
        ];
    }
}
