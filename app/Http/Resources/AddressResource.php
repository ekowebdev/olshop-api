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
                'name' => $this->province->name
            ],
            'city' => [
                'id' => $this->city->city_id,
                'name' => $this->city->name
            ],
            'subdistrict' => [
                'id' => $this->subdistrict->subdistrict_id,
                'name' => $this->subdistrict->name
            ],
            'postal_code' => $this->postal_code,
            'street' => $this->street,
            'is_main' => $this->is_main,
            'users' => $this->users->makeHidden(['email_verified_at', 'google_access_token', 'created_at', 'updated_at']),
        ];
    }
}
