<?php

namespace App\Http\Services;

use App\Http\Models\Cart;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Http\Models\Variant;
use App\Http\Models\ItemGift;
use Illuminate\Support\Facades\DB;
use App\Exceptions\ValidationException;
use Illuminate\Database\QueryException;
use App\Http\Repositories\CartRepository;
use Aws\DynamoDb\Exception\DynamoDbException;

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
        return $this->repository->getIndexData($locale);
    }

    public function getSingleData($locale, $id)
    {
        return $this->repository->getSingleData($locale, $id);
    }

    public function getDataByUser($locale, $id)
    {
        return $this->repository->getDataByUser($locale, $id);
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

        try {
            DB::beginTransaction();
            
            $user = auth()->user();
            $data_request['user_id'] = $user->id;
        
            $item_gift = ItemGift::find($data_request['item_gift_id']);
            $variant_id = isset($data_request['variant_id']) ? intval($data_request['variant_id']) : null;
            
            if ($variant_id) {
                $variant = Variant::where('id', $variant_id)->where('item_gift_id', $item_gift->id)->first();
        
                if (is_null($variant) || $variant->variant_quantity < $data_request['cart_quantity']) {
                    return response()->json([
                        'message' => is_null($variant)
                            ? trans('error.variant_not_found_in_item_gifts', ['id' => $item_gift->id])
                            : trans('error.variant_out_of_stock', ['id' => $item_gift->id, 'variant_id' => $variant->id]),
                        'status' => 400,
                    ], 400);
                }
            } elseif ($item_gift->variants->count() > 0) {
                return response()->json([
                    'message' => trans('error.variant_required', ['id' => $item_gift->id]),
                    'status' => 400,
                ], 400);
            }
        
            if ($item_gift->item_gift_quantity < $data_request['cart_quantity']) {
                return response()->json([
                    'message' => trans('error.out_of_stock', ['id' => $item_gift->id]),
                    'status' => 400,
                ], 400);
            }
        
            $cart = $this->repository->getByItemAndVariant(intval($item_gift->id), $variant_id)->first();
        
            if (!empty($cart)) {
                $cart->update([
                    'cart_quantity' => $cart->cart_quantity + $data_request['cart_quantity'],
                ]);
            } else {
                $cart_model = $this->model;
                $cart_model->id = strval(Str::uuid());
                $cart_model->user_id = intval($user->id);
                $cart_model->item_gift_id = intval($data_request['item_gift_id']);
                $cart_model->variant_id = $variant_id;
                $cart_model->cart_quantity = intval($data_request['cart_quantity']);
                $cart_model->save();
            }
        
            DB::commit();
        
            return response()->json([
                'message' => !empty($cart) ? trans('all.success_update_cart') : trans('all.success_add_to_cart'),
                'status' => 200,
                'error' => 0,
            ]);
        } catch (DynamoDbException $e) {
            DB::rollback();
            throw new ValidationException(json_encode([$e->getMessage()]));
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
