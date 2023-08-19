<?php

namespace App\Http\Services;

use Image;
use App\Http\Models\Redeem;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Http\Models\Wishlist;
use Illuminate\Support\Facades\DB;
use App\Http\Models\RedeemItemGift;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\RedeemResource;
use App\Exceptions\ValidationException;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Repositories\RatingRepository;
use App\Http\Repositories\RedeemItemGiftRepository;

class RedeemService extends BaseService
{
    private $model, $repository;
    
    public function __construct(Redeem $model, RedeemRepository $repository)
    {
        $this->model = $model;
        $this->repository = $repository;
    }

    public function getIndexData($locale, $data)
    {
        $search = [
            'redeem_code' => 'redeem_code',
            'total_point' => 'total_point'
        ];

        $search_column = [
            'id' => 'id',
            'redeem_code' => 'redeem_code',
            'total_point' => 'total_point'
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
        $item_gift = $this->repository->getSingleData($locale, $id);

        $data_request = Arr::only($data, [
            'item_gift_id',
            'redeem_quantity',
        ]);

        $this->repository->validate($data_request, [
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
            'redeem_code' => Str::uuid(),
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

        // $redeem = dispatch(new RedeemJob($locale, $id, $data));

        return response()->json([
            'message' => trans('all.success_redeem'),
            'status' => 200,
            'error' => 0
        ]);
    }

    public function redeem_multiple($locale, $data)
    {
        $data_request = Arr::only($data, [
            'item_gift_id',
            'redeem_quantity',
        ]);

        $this->repository->validate($data_request, [
                'item_gift_id' => [
                    'required'
                ],
                'item_gift_id.*' => [
                    'required',
                    'exists:item_gifts,id'
                ],
                'redeem_quantity' => [
                    'required',
                ],
                'redeem_quantity.*' => [
                    'required',
                    'numeric'
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
            $item_gift = $this->repository->getSingleData($locale, $item_gift_id);
            if (!$item_gift || $item_gift->item_gift_quantity < $quantity || $item_gift->item_gift_status == 'O') {
                DB::rollBack();
                throw new ValidationException(json_encode(['item_gift_id' => [trans('error.out_of_stock', ['id' => $item_gift_id])]]));
            }
            $subtotal = $item_gift->item_gift_point * $quantity;
            $total_point += $subtotal;
            $redeem_item_gift = new RedeemItemGift([
                'item_gift_id' => $item_gift->id,
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
