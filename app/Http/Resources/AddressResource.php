<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AddressResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'province_id' => $this->province_id,
            'city_id' => $this->city_id,
            'district_id' => $this->district_id,
            'postal_code' => $this->postal_code,
            'address' => $this->address,
            'users' => $this->users->makeHidden(['created_at', 'updated_at']),
        ];
    }
}
