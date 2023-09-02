<?php

namespace App\Http\Resources;

use App\Http\Resources\UserResource;
use Illuminate\Http\Resources\Json\JsonResource;

class RedeemResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'redeem_code' => $this->redeem_code,
            'redeem_item_gifts' => $this->redeem_item_gifts->map(function ($redeem_item_gift) {
                return [
                    'redeem_id' => $redeem_item_gift->redeem_id,
                    'redeem_quantity' => $redeem_item_gift->redeem_quantity,
                    'redeem_point' => $redeem_item_gift->redeem_point,
                    'item_gifts' => [
                        'id' => $redeem_item_gift->item_gifts->id,
                        'item_gift_code' => $redeem_item_gift->item_gifts->item_gift_code,
                        'item_gift_name' => $redeem_item_gift->item_gifts->item_gift_name,
                        'category' => ($redeem_item_gift->item_gifts->category_id != null) ? $redeem_item_gift->item_gifts->category->makeHidden(['created_at', 'updated_at']) : null,
                        'brand' => ($redeem_item_gift->item_gifts->brand_id != null) ? $redeem_item_gift->item_gifts->brand->makeHidden(['created_at', 'updated_at']) : null,
                        'item_gift_description' => $redeem_item_gift->item_gifts->item_gift_description,
                        'item_gift_point' => ($redeem_item_gift->item_gifts->variants->count() > 0) 
                            ? array_unique([
                                min($redeem_item_gift->item_gifts->variants->pluck('variant_point')->toArray()),
                                max($redeem_item_gift->item_gifts->variants->pluck('variant_point')->toArray()),
                              ])
                            : [$redeem_item_gift->item_gifts->item_gift_point],
                        'item_gift_quantity' => ($redeem_item_gift->item_gifts->variants->count() > 0) 
                            ? $redeem_item_gift->item_gifts->variants->sum('variant_quantity')
                            : $redeem_item_gift->item_gifts->item_gift_quantity,
                        'item_gift_status' => $redeem_item_gift->item_gifts->item_gift_status,
                        'item_gift_images' => $redeem_item_gift->item_gifts->item_gift_images->map(function ($image) {
                            return [
                                'item_gift_id' => $image->item_gift_id,
                                'item_gift_image_url' => $image->item_gift_image_url,
                            ];
                        }),
                        'variants' => $redeem_item_gift->item_gifts->variants->makeHidden(['created_at', 'updated_at']),
                    ],
                ];
            }),
            'total_point' => $this->total_point,
            'redeem_date' => $this->redeem_date,
            'users' => $this->users->makeHidden(['created_at', 'updated_at']),
        ];
    }
}
