<?php

namespace App\Http\Services;

use App\Http\Models\Cart;
use Illuminate\Support\Arr;
use App\Http\Models\Variant;
use App\Http\Models\ItemGift;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use App\Http\Repositories\CartRepository;

class CartService extends BaseService
{
    private $model, $repository;
    
    public function __construct(Cart $model, CartRepository $repository)
    {
        $this->model = $model;
        $this->repository = $repository;
    }

    public function getIndexData($locale, $data)
    {
        $search = [
            'user_id' => 'user_id',
            'item_gift_id' => 'item_gift_id',
            'variant_id' => 'variant_id',
            'cart_quantity' => 'cart_quantity',
        ];

        $search_column = [
            'id' => 'id',
            'user_id' => 'user_id',
            'item_gift_id' => 'item_gift_id',
            'variant_id' => 'variant_id',
            'cart_quantity' => 'cart_quantity',
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

    public function store($locale, $data)
    {
        $data_request = Arr::only($data, [
            'item_gift_id',
            'variant_id',
            'cart_quantity',
        ]);

        $this->repository->validate($data_request, [
                'item_gift_id' => [
                    'required',
                    'exists:item_gifts,id',
                ],
                'variant_id' => [
                    'nullable',
                    'exists:variants,id',
                ],
                'cart_quantity' => [
                    'required',
                    'numeric',
                ],
            ]
        );

        DB::beginTransaction();
        try {
            $data_request['user_id'] = auth()->user()->id;
            $item_gift = ItemGift::find($data_request['item_gift_id']);
            $cart = $this->repository->getByItemAndVariant($item_gift->id, $data_request['variant_id'] ?? null);
            if(isset($data_request['variant_id'])){
                $variant = Variant::where('id', $data_request['variant_id'])->where('item_gift_id', $item_gift->id)->first();
                if(is_null($variant)) {
                    return response()->json([
                        'message' => trans('error.variant_not_found_in_item_gifts', ['id' => $item_gift->id]),
                        'status' => 400,
                    ], 400);
                } else {
                    if($variant->variant_quantity < $data_request['cart_quantity']){
                        return response()->json([
                            'message' => trans('error.variant_out_of_stock', ['id' => $item_gift->id, 'variant_id' => $variant->id]),
                            'status' => 400,
                        ], 400);
                    }
                }
                if ($item_gift->variants->count() < 0) {
                    return response()->json([
                        'message' => trans('error.variant_not_found_in_item_gifts', ['id' => $item_gift->id]),
                        'status' => 400,
                    ], 400);
                }
            }
            if ($item_gift->variants->count() > 0) {
                if (!isset($data_request['variant_id'])) {
                    return response()->json([
                        'message' => trans('error.variant_required', ['id' => $item_gift->id]),
                        'status' => 400,
                    ], 400);
                }
            }
            if($item_gift->item_gift_quantity < $data_request['cart_quantity']){
                return response()->json([
                    'message' => trans('error.out_of_stock', ['id' => $item_gift->id]),
                    'status' => 400,
                ], 400);
            }
            if(!empty($cart)){
                $cart->update([
                    'cart_quantity' => $cart->cart_quantity + $data_request['cart_quantity'],
                ]);
                DB::commit();
                return response()->json([
                    'message' => trans('all.success_update_cart'),
                    'status' => 200,
                    'error' => 0,
                ]);
            }
            $this->model->create($data_request);
            DB::commit();
            return response()->json([
                'message' => trans('all.success_add_to_cart'),
                'status' => 200,
                'error' => 0,
            ]);
        } catch (QueryException $e) {
            DB::rollback();
        }
    }

    public function delete($locale, $id)
    {
        $check_data = $this->repository->getSingleData($locale, $id);
        
        DB::beginTransaction();
        $result = $check_data->delete();
        DB::commit();

        return $result;
    }
}
