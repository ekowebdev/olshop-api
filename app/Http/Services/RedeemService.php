<?php

namespace App\Http\Services;

use App\Http\Models\Redeem;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Http\Models\Variant;
use Illuminate\Support\Facades\DB;
use App\Http\Models\RedeemItemGift;
use App\Http\Services\RedeemService;
use App\Exceptions\ValidationException;
use Illuminate\Database\QueryException;
use App\Http\Repositories\RedeemRepository;
use App\Http\Repositories\ItemGiftRepository;

class RedeemService extends BaseService
{
    private $model, $repository, $item_gift_repository;
    
    public function __construct(Redeem $model, RedeemRepository $repository, ItemGiftRepository $item_gift_repository)
    {
        $this->model = $model;
        $this->repository = $repository;
        $this->item_gift_repository = $item_gift_repository;
    }

    public function getIndexData($locale, $data)
    {
        $search = [
            'redeem_code' => 'redeem_code',
            'user_id' => 'user_id',
            'total_point' => 'total_point',
        ];

        $search_column = [
            'id' => 'id',
            'redeem_code' => 'redeem_code',
            'user_id' => 'user_id',
            'total_point' => 'total_point',
        ];

        $sortable_and_searchable_column = [
            'search'        => $search,
            'search_column' => $search_column,
            'sort_column'   => array_merge($search, $search_column),
        ];
        
        return $this->repository->getIndexData($locale, $sortable_and_searchable_column);
    }

    public function getSingleData($locale, $id)
    {
        return $this->repository->getSingleData($locale, $id);
    }

    public function redeem($locale, $id, $data)
    {
        $item_gift = $this->item_gift_repository->getSingleData($locale, $id);

        $data_request = Arr::only($data, [
            'variant_id',
            'redeem_quantity',
        ]);

        $this->item_gift_repository->validate($data_request, [
                'variant_id' => [
                    'exists:variants,id'
                ],
                'redeem_quantity' => [
                    'required',
                    'numeric',
                    'min:1',
                ],
            ]
        );

        DB::beginTransaction();
        try {
            $item_gift_variant = null;
            $variant_locked = false;
            $total_point = 0;

            if ($item_gift->variants->count() > 0 && !is_null($data_request['variant_id'])) {
                $item_gift_variant = $item_gift->variants()->lockForUpdate()->find($data_request['variant_id'] ?? 0);

                if (is_null($item_gift_variant)) {
                    return response()->json([
                        'message' => trans('error.variant_not_found_in_item_gifts'),
                        'status' => 400,
                        'error' => 0,
                    ]);
                }

                // Lock the variants row for update
                $variant_locked = true;
            }

            // Lock the item_gifts row for update
            $item_gift->lockForUpdate()->get();

            if ($item_gift->item_gift_quantity == 0) {
                $item_gift->update(['item_gift_status' => 'O']);
            }

            if ($item_gift_variant) {
                $total_point = $item_gift_variant->variant_point * $data_request['redeem_quantity'];
            } else {
                $total_point = $item_gift->item_gift_point * $data_request['redeem_quantity'];
            }

            if (!$item_gift || $item_gift->item_gift_quantity < $data_request['redeem_quantity'] || $item_gift->item_gift_status == 'O') {
                return response()->json([
                    'message' => trans('error.variant_out_of_stock', ['id' => $item_gift->id, 'variant_id' => $item_gift_variant->id]),
                    'status' => 400,
                    'error' => 0,
                ], 400);
            }

            $redeem = Redeem::create([
                'user_id' => auth()->user()->id,
                'redeem_code' => Str::uuid(),
                'total_point' => $total_point,
                'redeem_date' => date('Y-m-d'),
            ]);

            $redeem_item_gift = new RedeemItemGift([
                'item_gift_id' => $item_gift->id,
                'variant_id' => $data_request['variant_id'] ?? null,
                'redeem_quantity' => $data_request['redeem_quantity'],
                'redeem_point' => $total_point,
            ]);

            $redeem->redeem_item_gifts()->save($redeem_item_gift);

            $item_gift->item_gift_quantity -= $data_request['redeem_quantity'];
            $item_gift->save();

            if ($variant_locked) {
                if ($item_gift_variant->variant_quantity == 0 || $item_gift_variant->variant_quantity < $data_request['redeem_quantity']) {
                    return response()->json([
                        'message' => trans('error.variant_out_of_stock', ['id' => $item_gift->id, 'variant_id' => $item_gift_variant->id]),
                        'status' => 400,
                        'error' => 0,
                    ], 400);
                }

                $item_gift_variant->update([
                    'variant_quantity' => $item_gift_variant->variant_quantity - $data_request['redeem_quantity'],
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => trans('all.success_redeem'),
                'status' => 200,
                'error' => 0,
            ]);
        } catch (QueryException $e) {
            DB::rollback();
        }
    }

    public function redeem_multiple($locale, $data)
    {
        $data_request = Arr::only($data, [
            'item_gift_id',
            'redeem_quantity',
        ]);

        $this->item_gift_repository->validate($data_request, [
                'item_gift_id' => [
                    'required',
                ],
                'item_gift_id.*' => [
                    'required',
                    'exists:item_gifts,id',
                ],
                'variant_id.*' => [
                    'exists:variants,id',
                ],
                'redeem_quantity' => [
                    'required',
                ],
                'redeem_quantity.*' => [
                    'required',
                    'numeric',
                    'min:1',
                ],
            ]
        );

        DB::beginTransaction();
        $total_point = 0;
        $redeem = Redeem::create([
            'user_id' => auth()->user()->id,
            'redeem_code' => Str::uuid(),
            'total_point' => $total_point,
            'redeem_date' => date('Y-m-d'),
        ]);
        foreach ($data_request['item_gift_id'] as $key => $item_gift_id) {
            $quantity = $data_request['redeem_quantity'][$key];
            $item_gift = $this->item_gift_repository->getSingleData($locale, $item_gift_id);
            if (!$item_gift || $item_gift->item_gift_quantity < $quantity || $item_gift->item_gift_status == 'O') {
                throw new ValidationException(json_encode(['item_gift_id' => [trans('error.out_of_stock', ['id' => $item_gift_id])]]));
            }
            $subtotal = $item_gift->item_gift_point * $quantity;
            $total_point += $subtotal;
            $redeem_item_gift = new RedeemItemGift([
                'item_gift_id' => $item_gift->id,
                'variant_id' => $item_gift->id,
                'redeem_quantity' => $quantity,
                'redeem_point' => $subtotal,
            ]);
            $redeem->redeem_item_gifts()->save($redeem_item_gift);
            $item_gift->item_gift_quantity -= $quantity;
            $item_gift->save();
        }
        $redeem->total_point = $total_point;
        $redeem->save();
        DB::commit();

        return response()->json([
            'message' => trans('all.success_redeem'),
            'status' => 200,
            'error' => 0
        ]);
    }
}
