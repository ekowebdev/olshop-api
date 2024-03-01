<?php

namespace App\Http\Services;

use App\Http\Models\Cart;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Http\Models\Variant;
use App\Http\Models\Product;
use Illuminate\Support\Facades\DB;
use App\Exceptions\ValidationException;
use Illuminate\Database\QueryException;
use App\Exceptions\ApplicationException;
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
            'product_id',
            'variant_id',
            'quantity',
        ]);

        $this->repository->validate($data_request, [
                'product_id' => [
                    'required',
                    'exists:products,id',
                ],
                'variant_id' => [
                    'nullable',
                    'exists:variants,id',
                ],
                'quantity' => [
                    'required',
                    'numeric',
                ],
            ]
        );

        try {
            DB::beginTransaction();
            
            $user = auth()->user();
            $data_request['user_id'] = $user->id;

            $variant_id = isset($data_request['variant_id']) ? intval($data_request['variant_id']) : null;
        
            $product = Product::lockForUpdate()->find($data_request['product_id']);

            if ($product->variants->count() > 0 && !isset($variant_id)) {
                return response()->json([
                    'message' => trans('error.variant_required', ['id' => $product->id]),
                    'status' => 400,
                ], 400);
            } else if ($product->variants->count() == 0 && isset($variant_id)) {
                return response()->json([
                    'message' => trans('error.variant_not_found_in_products', ['id' => $product->id]),
                    'status' => 400,
                ], 400);
            }

            if(!$product || $product->quantity < $data_request['quantity'] || $product->status == 'O'){
                return response()->json([
                    'message' => trans('error.out_of_stock'),
                    'status' => 400,
                ], 400);
            }
            
            if (!is_null($variant_id)) {
                $variant = $product->variants()->lockForUpdate()->find($variant_id);

                if (is_null($variant)) {
                    return response()->json([
                        'message' => trans('error.variant_not_available_in_products', ['id' => $product->id, 'variant_id' => $variant_id]),
                        'status' => 400,
                    ], 400);
                }

                if ($variant->quantity == 0 || $data_request['quantity'] > $variant->quantity) {
                    return response()->json([
                        'message' => trans('error.out_of_stock'),
                        'status' => 400,
                    ], 400);
                }
            }

            $exists_cart = $this->repository->getByUserProductAndVariant($user->id, $data_request['product_id'], $variant_id)->first();
            
            if(!empty($exists_cart)) {
                $quantity = $exists_cart->quantity + intval($data_request['quantity']);

                if($product->variants->count() > 0){
                    $real_quantity = $variant->quantity;
                } else {
                    $real_quantity = $product->quantity;
                }
            
                if ($quantity > $real_quantity) {
                    return response()->json([
                        'message' => trans('error.out_of_stock'),
                        'status' => 400,
                    ], 400);
                }

                $exists_cart->update([
                    'quantity' => $exists_cart->quantity + $data_request['quantity'],
                ]);
            } else {
                $cart = $this->model;
                $cart->id = strval(Str::uuid());
                $cart->user_id = intval($user->id);
                $cart->product_id = intval($data_request['product_id']);
                $cart->variant_id = $variant_id ?? '';
                $cart->quantity = intval($data_request['quantity']);
                $cart->save();
            }
        
            DB::commit();
        
            return response()->json([
                'message' => trans('all.success_add_to_cart'),
                'status' => 200,
                'error' => 0,
            ]);
        } catch (DynamoDbException $e) {
            DB::rollback();
            throw new ApplicationException(json_encode([$e->getMessage()]));
        }        
    }

    public function update($locale, $id, $data)
    {
        $check_data = $this->repository->getSingleData($locale, $id);

        $data = array_merge([
            'quantity' => $check_data->quantity,
        ], $data);

        $data_request = Arr::only($data, [
            'quantity',
        ]);

        $this->repository->validate($data, [
            'quantity' => [
                'numeric',
            ],
        ]);

        DB::beginTransaction();

        $quantity = $data_request['quantity'];
        $product = Product::find($check_data->product_id);

        if($product->variants->count() > 0){
            $variant = Variant::where('id', $check_data->variant_id)->where('product_id', $product->id)->first();
            $real_quantity = $variant->quantity;
        } else {
            $real_quantity = $product->quantity;
        }
    
        if ($quantity > $real_quantity) {
            throw new ValidationException(json_encode(['quantity' => [trans('error.out_of_stock')]]));
        }

        $data_request['quantity'] = intval($data_request['quantity']);
        $check_data->update($data_request);

        DB::commit();

        return $this->repository->getSingleData($locale, $id);
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
