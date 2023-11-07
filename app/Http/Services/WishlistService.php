<?php

namespace App\Http\Services;

use Illuminate\Support\Str;
use App\Http\Models\Wishlist;
use Illuminate\Support\Facades\DB;
use App\Http\Repositories\ItemGiftRepository;
use App\Http\Repositories\WishlistRepository;

class WishlistService extends BaseService
{
    private $model, $repository, $item_gift_repository;
    
    public function __construct(Wishlist $model, WishlistRepository $repository, ItemGiftRepository $item_gift_repository)
    {
        $this->model = $model;
        $this->repository = $repository;
        $this->item_gift_repository = $item_gift_repository;
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
        $item_gift = $this->item_gift_repository->getSingleData($locale, $id);
        $user = auth()->user();
        $check_wishlist = $this->repository->getDataByUserAndItem($locale, $item_gift->id)->first();

        DB::beginTransaction();
        
        if (is_null($check_wishlist)) {
            $wishlist_model = $this->model;
            $wishlist_model->id = strval(Str::uuid());
            $wishlist_model->user_id = $user->id;
            $wishlist_model->item_gift_id = $item_gift->id;
            $wishlist_model->save();

            $message = trans('all.success_add_to_wishlists');
        } else {
            $check_wishlist->delete();
            $message = trans('all.success_delete_from_wishlists');
        }

        DB::commit();

        return response()->json([
            'message' => $message,
            'status' => 200,
            'error' => 0
        ]);
    }
}
