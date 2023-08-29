<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ItemGiftResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'item_gift_code' => $this->item_gift_code,
            'item_gift_name' => $this->item_gift_name,
            'item_gift_description' => $this->item_gift_description,
            'item_gift_point' => $this->item_gift_point,
            'item_gift_quantity' => $this->item_gift_quantity,
            'item_gift_status' => $this->item_gift_status,
            'item_gift_images' => $this->item_gift_images->makeHidden(['created_at', 'updated_at']),
            'reviews' => $this->reviews->map(function ($review) {
                return [
                    'id' => $review->id,
                    'users' => [
                        'id' => $review->users->id,
                        'name' => $review->users->name,
                    ],
                    'item_gift_id' => $review->item_gift_id,
                    'review_text' => $review->review_text,
                    'review_rating' => $review->review_rating,
                    'review_date' => $review->review_date,
                ];
            }),
            'total_reviews' => $this->total_reviews,
            'total_rating' => floatval(rtrim($this->total_rating, '0')),
            'is_wishlist' => $this->is_wishlist
        ];
    }
}
