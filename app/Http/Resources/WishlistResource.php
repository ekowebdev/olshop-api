<?php

namespace App\Http\Resources;

use App\Http\Resources\UserResource;
use Illuminate\Http\Resources\Json\JsonResource;

class WishlistResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'item_gifts' => $this->item_gifts->makeHidden(['created_at', 'updated_at']),
            'users' => $this->users->makeHidden(['created_at', 'updated_at']),
        ];
    }
}
