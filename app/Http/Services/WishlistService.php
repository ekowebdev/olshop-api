<?php

namespace App\Http\Services;

use Illuminate\Support\Str;
use App\Http\Models\Wishlist;
use Illuminate\Support\Facades\DB;
use App\Http\Repositories\ProductRepository;
use App\Http\Repositories\WishlistRepository;

class WishlistService extends BaseService
{
    private $model, $repository, $product_repository;

    public function __construct(Wishlist $model, WishlistRepository $repository, ProductRepository $product_repository)
    {
        $this->model = $model;
        $this->repository = $repository;
        $this->product_repository = $product_repository;
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

    public function wishlist($locale, $id, $data)
    {
        DB::beginTransaction();

        $product = $this->product_repository->getSingleData($locale, $id);
        $user = auth()->user();

        $check_wishlist = $this->repository->getDataByUserAndProduct($locale, $product->id)->first();

        if(is_null($check_wishlist)) {
            $wishlist = $this->model;
            $wishlist->id = Str::uuid();
            $wishlist->user_id = $user->id;
            $wishlist->product_id = $product->id;
            $wishlist->save();
            $message = trans('all.success_add_to_wishlists', ['product_name' => $product->name]);
        } else {
            $check_wishlist->delete();
            $message = trans('all.success_delete_from_wishlists', ['product_name' => $product->name]);
        }

        DB::commit();

        return response()->api($message);
    }
}
