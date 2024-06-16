<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'text' => $this->text,
            'url' => $this->url,
            'type' => $this->type,
            'icon' => $this->icon,
            'background_color' => $this->background_color,
            'status_read' => $this->status_read,
            'users' => (!$this->users) ? null : $this->users->makeHidden(['email_verified_at', 'google_access_token', 'created_at', 'updated_at']),
        ];
    }
}
