<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AddressResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'person_name' => $this->person_name,
            'person_phone' => $this->person_phone,
            'province' => [
                'id' => $this->province->province_id,
                'province_name' => $this->province->province_name
            ],
            'city' => [
                'id' => $this->city->city_id,
                'city_name' => $this->city->city_name
            ],
            'subdistrict' => [
                'id' => $this->subdistrict->subdistrict_id,
                'subdistrict_name' => $this->subdistrict->subdistrict_name
            ],
            'postal_code' => $this->postal_code,
            'address' => $this->address,
            'is_main' => $this->is_main,
            'users' => $this->users->makeHidden(['email_verified_at', 'created_at', 'updated_at']),
        ];
    }
}
