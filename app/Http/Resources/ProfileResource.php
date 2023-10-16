<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'birthdate' => $this->birthdate,
            'phone_number' => $this->phone_number,
            'avatar' => $this->avatar,
            'avatar_url' => $this->avatar_url,
            'users' => $this->users->makeHidden(['email_verified_at', 'created_at', 'updated_at']),
        ];
    }
}
