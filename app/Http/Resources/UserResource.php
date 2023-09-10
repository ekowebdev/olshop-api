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
            'address' => ($this->address) ? [
                'province_id' => $this->address->province_id,
                'city_id' => $this->address->city_id,
                'district_id' => $this->address->district_id,
                'postal_code' => $this->address->postal_code,
                'address' => $this->address->address,
            ] : null,
        ];
    }
}
