<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'roles' => $this->getRoleNames(),
            'username' => $this->username,
            'email' => $this->email,
            'birthdate' => $this->birthdate,
            'address' => ($this->address) ? [
                'province' => [
                    'id' => $this->address->province->province_id,
                    'province_name' => $this->address->province->province_name
                ],
                'city' => [
                    'id' => $this->address->city->city_id,
                    'city_name' => $this->address->city->city_name
                ],
                'subdistrict' => [
                    'id' => $this->address->subdistrict->subdistrict_id,
                    'subdistrict_name' => $this->address->subdistrict->subdistrict_name
                ],
                'postal_code' => $this->address->postal_code,
                'address' => $this->address->address,
            ] : null,
        ];
    }
}
