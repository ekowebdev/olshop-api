<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'birthdate' => $this->birthdate,
            'phone_number' => $this->phone_number,
            'avatar' => $this->avatar,
            'avatar_url' => $this->avatar_url,
            'avatar_thumb_url' => $this->avatar_thumb_url,
            'users' => $this->users->makeHidden(['email_verified_at', 'google_access_token', 'created_at', 'updated_at']),
        ];
    }
}
