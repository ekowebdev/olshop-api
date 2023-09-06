<?php

namespace App\Http\Services;

use App\Http\Models\Cart;
use Illuminate\Support\Arr;
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
            if($data_request['variant_id']){
                $item_gift = ItemGift::find($data_request['item_gift_id']);
                if ($item_gift->variants->count() < 1) {
                    return response()->json([
                        'message' => trans('error.variant_not_found_in_item_gifts'),
                        'status' => 400,
                        'error' => 0,
                    ], 400);
                }
            }
            $result = $this->model->create($data_request);
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

    public function update($locale, $id, $data)
    {
        $check_data = $this->repository->getSingleData($locale, $id);

        $data = array_merge([
            'item_gift_id' => $check_data->item_gift_id,
            'variant_id' => $check_data->variant_id,
            'cart_quantity' => $check_data->cart_quantity,
        ], $data);

        $data_request = Arr::only($data, [
            'item_gift_id',
            'variant_id',
            'cart_quantity',
        ]);

        $this->repository->validate($data_request, [
                'item_gift_id' => [
                    'exists:item_gifts,id',
                ],
                'variant_id' => [
                    'nullable',
                    'exists:variants,id',
                ],
                'cart_quantity' => [
                    'numeric',
                ]
            ]
        );

        DB::beginTransaction();
        try {
            if($data_request['variant_id']){
                if($data_request['item_gift_id']){
                    $item_gift = ItemGift::find($data_request['item_gift_id']);
                } else {
                    $item_gift = ItemGift::find($check_data->item_gift_id);
                }
                if ($item_gift->variants->count() == 0) {
                    return response()->json([
                        'message' => trans('error.variant_not_found_in_item_gifts'),
                        'status' => 400,
                        'error' => 0,
                    ], 400);
                }
            }
            $check_data->update($data_request);
            DB::commit();

            return response()->json([
                'message' => trans('all.success_update_cart'),
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
