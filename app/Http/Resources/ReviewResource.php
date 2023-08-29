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
            'item_gifts' => [
                'id' => $this->item_gifts->id,
                'item_gift_code' => $this->item_gifts->item_gift_code,
                'item_gift_name' => $this->item_gifts->item_gift_name,
                'item_gift_description' => $this->item_gifts->item_gift_description,
                'item_gift_point' => $this->item_gifts->item_gift_point,
                'item_gift_quantity' => $this->item_gifts->item_gift_quantity,
                'item_gift_status' => $this->item_gifts->item_gift_status,
                'item_gift_images' => $this->item_gifts->item_gift_images->map(function ($image) {
                    return [
                        'item_gift_id' => $image->item_gift_id,
                        'item_gift_image_url' => $image->item_gift_image_url,
                    ];
                }),
            ],
            'users' => $this->users->makeHidden(['created_at', 'updated_at']),
        ];
    }
}
