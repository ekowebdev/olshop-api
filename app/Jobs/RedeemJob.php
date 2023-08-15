<?php

namespace App\Jobs;

use App\Http\Models\Redeem;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Http\Models\ItemGift;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use App\Http\Models\RedeemItemGift;
use Illuminate\Queue\SerializesModels;
use App\Exceptions\ValidationException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Http\Repositories\ItemGiftRepository;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class RedeemJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $locale, $id, $data;

    public function __construct($locale, $id, $data)
    {
        $this->locale = $locale;
        $this->id = $id;
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(ItemGiftRepository $repository)
    {
        $item_gift = $repository->getSingleData($this->locale, $this->id);

        $data_request = Arr::only($this->data, [
            'item_gift_id',
            'redeem_quantity',
        ]);

        $repository->validate($data_request, [
                'redeem_quantity' => [
                    'required',
                    'numeric'
                ],
            ]
        );

        DB::beginTransaction();
        if($item_gift->item_gift_quantity == 0) {
            $item_gift->update([
                'item_gift_status' => 'O'
            ]);
        }
        $total_point = 0;
        $redeem = Redeem::create([
            'user_id' => auth()->user()->id,
            'redeem_code' => Str::random(20),
            'total_point' => $total_point,
            'redeem_date' => date('Y-m-d'),
        ]);
        if (!$item_gift || $item_gift->item_gift_quantity < $data_request['redeem_quantity'] || $item_gift->item_gift_status == 'O') {
            DB::rollBack();
            throw new ValidationException(json_encode(['item_gift_id' => [trans('error.out_of_stock', ['id' => $item_gift_id])]]));
        }
        $subtotal = $item_gift->item_gift_point * $data_request['redeem_quantity'];
        $total_point += $subtotal;
        $redeem_item_gift = new RedeemItemGift([
            'item_gift_id' => $item_gift->id,
            'redeem_quantity' => $data_request['redeem_quantity'],
            'redeem_point' => $subtotal,
        ]);
        $redeem->redeem_item_gifts()->save($redeem_item_gift);
        $item_gift->item_gift_quantity -= $data_request['redeem_quantity'];
        $item_gift->save();
        $redeem->total_point = $total_point;
        $redeem->save();
        DB::commit();

        return $redeem;
    }
}
