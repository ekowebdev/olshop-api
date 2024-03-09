<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SubdistrictResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->d,
            'city' => [
                'id' => $this->city->id,
                'name' => $this->city->name,
            ],
            'name' => $this->name
        ];
    }
}
