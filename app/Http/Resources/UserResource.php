<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'roles' => $this->getRoleNames(),
            'username' => $this->username,
            'google_id' => $this->google_id,
            'email' => $this->email,
            'email_status' => $this->email_verified_at != null ? 'verified' : 'unverified',
            'email_verified_at' => $this->email_verified_at,
            'has_password' => $this->has_password,
            'profile' => ($this->profile) ? [
                'id' => $this->profile->id,
                'name' => $this->profile->name,
                'birthdate' => $this->profile->birthdate,
                'phone_number' => $this->profile->phone_number,
                'avatar' => $this->profile->avatar,
                'avatar_url' => $this->profile->avatar_url,
            ] : null,
            'main_address' => ($this->main_address) ? [
                'id' => $this->main_address->id,
                'person_name' => $this->main_address->person_name,
                'person_phone' => $this->main_address->person_phone,
                'province' => [
                    'id' => $this->main_address->province->id,
                    'name' => $this->main_address->province->name
                ],
                'city' => [
                    'id' => $this->main_address->city->id,
                    'name' => $this->main_address->city->name
                ],
                'subdistrict' => [
                    'id' => $this->main_address->subdistrict->id,
                    'name' => $this->main_address->subdistrict->name
                ],
                'postal_code' => $this->main_address->postal_code,
                'street' => $this->main_address->street,
            ] : null,
            'address' => $this->address->map(function ($address) {
                return [
                    'id' => $address->id,
                    'person_name' => $address->person_name,
                    'person_phone' => $address->person_phone,
                    'province' => [
                        'id' => $address->province->id,
                        'name' => $address->province->name
                    ],
                    'city' => [
                        'id' => $address->city->id,
                        'name' => $address->city->name
                    ],
                    'subdistrict' => [
                        'id' => $address->subdistrict->id,
                        'name' => $address->subdistrict->name
                    ],
                    'postal_code' => $address->postal_code,
                    'street' => $address->street,
                    'is_main' => ($this->main_address_id == $address->id) ? 1 : 0,
                ];
            }),
        ];
    }
}
