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
                    'item_gift' => $redeem_item_gift->item_gifts->makeHidden(['created_at', 'updated_at']),
                ];
            }),
            'total_point' => $this->total_point,
            'redeem_date' => $this->redeem_date,
        ];
    }
}
