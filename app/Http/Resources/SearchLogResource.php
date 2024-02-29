<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SearchLogResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'search_text' => $this->search_text,
            'users' => $this->users->makeHidden(['email_verified_at', 'google_id', 'google_access_token', 'created_at', 'updated_at']),
        ];
    }
}
