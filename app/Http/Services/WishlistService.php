<?php

namespace App\Http\Services;

use Illuminate\Support\Str;
use App\Http\Models\Wishlist;
use Illuminate\Support\Facades\DB;
use App\Http\Repositories\ProductRepository;
use App\Http\Repositories\WishlistRepository;

class WishlistService extends BaseService
{
    private $model, $repository, $productRepository;

    public function __construct(Wishlist $model, WishlistRepository $repository, ProductRepository $productRepository)
    {
        $this->model = $model;
        $this->repository = $repository;
        $this->productRepository = $productRepository;
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

    // public function wishlist($locale, $id, $data)
    // {
    //     DB::beginTransaction();

    //     $product = $this->productRepository->getSingleData($locale, $id);

    //     $user = auth()->user();

    //     $checkWishlist = $this->repository->queryByUserIdAndProductId($product->id)->first();

    //     if(is_null($checkWishlist)) {
    //         $wishlist = $this->model;
    //         $wishlist->id = (string) Str::uuid();
    //         $wishlist->user_id = $user->id;
    //         $wishlist->product_id = $product->id;
    //         $wishlist->save();
    //         $message = trans('all.success_add_to_wishlists', ['product_name' => $product->name]);
    //     } else {
    //         $checkWishlist->delete();
    //         $message = trans('all.success_delete_from_wishlists', ['product_name' => $product->name]);
    //     }

    //     DB::commit();

    //     return response()->api($message);
    // }

    public function wishlist($locale, $id, $data)
    {
        DB::beginTransaction();

        try {
            $product = $this->productRepository->getSingleData($locale, $id);
            $userId = auth()->id();

            $checkWishlist = $this->repository->queryByUserIdAndProductId($product->id)->first();

            if (is_null($checkWishlist)) {
                $this->model->create([
                    'id' => (string) Str::uuid(),
                    'user_id' => $userId,
                    'product_id' => $product->id,
                ]);
                $message = trans('all.success_add_to_wishlists', ['product_name' => $product->name]);
            } else {
                $checkWishlist->delete();
                $message = trans('all.success_delete_from_wishlists', ['product_name' => $product->name]);
            }

            DB::commit();

            return response()->api($message);
        } catch (\Exception $e) {
            DB::rollback();
            throw new SystemException(json_encode([$e->getMessage()]));
        }
    }

}
