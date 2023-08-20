<?php

namespace App\Http\Resources;

use App\Http\Resources\UserResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'review_text' => $this->review_text,
            'review_rating' => $this->review_rating,
            'review_date' => $this->review_date,
            'item_gifts' => $this->item_gifts->makeHidden(['created_at', 'updated_at']),
        ];
    }
}
