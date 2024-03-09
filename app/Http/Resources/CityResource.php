<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CityResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'province' => [
                'id' => $this->province->id,
                'name' => $this->province->name,
            ],
            'name' => $this->name,
            'postal_code' => $this->postal_code
        ];
    }
}
