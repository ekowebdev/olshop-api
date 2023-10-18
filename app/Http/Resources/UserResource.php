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
            'profile' => ($this->profile) ? [
                'id' => $this->profile->id,
                'birthdate' => $this->profile->birthdate,
                'phone_number' => $this->profile->phone_number,
                'avatar' => $this->profile->avatar,
                'avatar_url' => $this->profile->avatar_url,
            ] : null,
            'main_address' => [
                'id' => $this->main_address->id,
                'person_name' => $this->main_address->person_name,
                'person_phone' => $this->main_address->person_phone,
                'province' => [
                    'id' => $this->main_address->province->province_id,
                    'province_name' => $this->main_address->province->province_name
                ],
                'city' => [
                    'id' => $this->main_address->city->city_id,
                    'city_name' => $this->main_address->city->city_name
                ],
                'subdistrict' => [
                    'id' => $this->main_address->subdistrict->subdistrict_id,
                    'subdistrict_name' => $this->main_address->subdistrict->subdistrict_name
                ],
                'postal_code' => $this->main_address->postal_code,
                'address' => $this->main_address->address,
            ],
            'address' => $this->address->map(function ($address) {
                return [
                    'id' => $address->id,
                    'person_name' => $address->person_name,
                    'person_phone' => $address->person_phone,
                    'province' => [
                        'id' => $address->province->province_id,
                        'province_name' => $address->province->province_name
                    ],
                    'city' => [
                        'id' => $address->city->city_id,
                        'city_name' => $address->city->city_name
                    ],
                    'subdistrict' => [
                        'id' => $address->subdistrict->subdistrict_id,
                        'subdistrict_name' => $address->subdistrict->subdistrict_name
                    ],
                    'postal_code' => $address->postal_code,
                    'address' => $address->address,
                    'is_main' => ($this->main_address_id == $address->id) ? 1 : 0,
                ];
            }),
        ];
    }
}
