<?php

namespace App\Http\Services;

use App\Http\Models\Cart;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Http\Models\Product;
use App\Http\Models\Variant;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\CartResource;
use App\Exceptions\ValidationException;
use App\Exceptions\ApplicationException;
use App\Http\Repositories\CartRepository;

class CartService extends BaseService
{
    private $model, $modelProduct, $modelVariant, $repository;

    public function __construct(Cart $model, Product $modelProduct, Variant $modelVariant, CartRepository $repository)
    {
        $this->model = $model;
        $this->modelProduct = $modelProduct;
        $this->modelVariant = $modelVariant;
        $this->repository = $repository;
    }

    public function index($locale, $data)
    {
        return $this->repository->getAllData($locale);
    }

    public function show($locale, $id)
    {
        return $this->repository->getSingleData($locale, $id);
    }

    public function showByUser($locale, $id)
    {
        return $this->repository->getDataByUser($locale, $id);
    }

    public function store($locale, $data)
    {
        $request = Arr::only($data, [
            'product_id',
            'variant_id',
            'quantity',
        ]);

        $this->repository->validate($request, [
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
            $request['user_id'] = $user->id;
            $variant_id = isset($request['variant_id']) ? (int) $request['variant_id'] : null;
            $product = $this->modelProduct->lockForUpdate()->find($request['product_id']);

            if ($product->variants->count() > 0 && !isset($variant_id)) throw new ValidationException(trans('error.variant_required', ['product_name' => $product->name]));

            else if ($product->variants->count() == 0 && isset($variant_id)) throw new ValidationException(trans('error.variant_not_found_in_products', ['product_name' => $product->name]));

            if(!$product || $product->quantity < $request['quantity'] || $product->status == 'O') throw new ApplicationException(trans('error.out_of_stock'));

            if (!is_null($variant_id)) {
                $variant = $product->variants()->lockForUpdate()->find($variant_id);
                $variant_name = $this->modelVariant->find($variant_id)->name;
                if (is_null($variant)) throw new ValidationException(trans('error.variant_not_available_in_products', ['product_name' => $product->name, 'variant_name' => $variant_name]));
                if ($variant->quantity == 0 || $request['quantity'] > $variant->quantity) throw new ApplicationException(trans('error.out_of_stock'));
            }

            $exists_cart = $this->repository->getByUserProductAndVariant($user->id, $request['product_id'], $variant_id)->first();

            if(!empty($exists_cart)) {
                $quantity = $exists_cart->quantity + (int) $request['quantity'];
                if($product->variants->count() > 0) $real_quantity = $variant->quantity;
                else $real_quantity = $product->quantity;
                if ($quantity > $real_quantity) throw new ApplicationException(trans('error.out_of_stock'));
                $exists_cart->update(['quantity' => $exists_cart->quantity + $request['quantity']]);
            } else {
                $cart = $this->model;
                $cart->id = (string) Str::uuid();
                $cart->user_id = (int) $user->id;
                $cart->product_id = (int) $request['product_id'];
                $cart->variant_id = $variant_id ?? '';
                $cart->quantity = (int) $request['quantity'];
                $cart->save();
            }

            DB::commit();

            return response()->api(trans('all.success_add_to_cart', ['product_name' => $product->name]));
        } catch (\Exception $e) {
            DB::rollback();
            throw new ApplicationException(json_encode([$e->getMessage()]));
        }
    }

    public function update($locale, $id, $data)
    {
        $checkData = $this->repository->getSingleData($locale, $id);

        $data = array_merge([
            'quantity' => $checkData->quantity,
        ], $data);

        $request = Arr::only($data, [
            'quantity',
        ]);

        $this->repository->validate($data, [
            'quantity' => [
                'numeric',
            ],
        ]);

        DB::beginTransaction();

        $quantity = $request['quantity'];
        $product = $this->modelProduct->find($checkData->product_id);

        if($product->variants->count() > 0){
            $variant = $this->modelVariant->where('id', $checkData->variant_id)->where('product_id', $product->id)->first();
            $real_quantity = $variant->quantity;
        } else {
            $real_quantity = $product->quantity;
        }

        if ($real_quantity < $quantity) throw new ApplicationException(trans('error.out_of_stock'));

        $request['quantity'] = (int) $request['quantity'];
        $checkData->update($request);

        DB::commit();

        return $this->repository->getSingleData($locale, $id);
    }

    public function delete($locale, $id)
    {
        $checkData = $this->repository->getSingleData($locale, $id);

        DB::beginTransaction();

        $result = $checkData->delete();

        DB::commit();

        return $result;
    }
}
