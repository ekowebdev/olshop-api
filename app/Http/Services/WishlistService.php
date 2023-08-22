<?php

namespace App\Http\Services;

use App\Http\Models\Wishlist;
use Illuminate\Support\Facades\DB;
use App\Exceptions\ValidationException;
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
        $search = [
            'user_id' => 'user_id',
            'item_gift_id' => 'item_gift_id',
        ];

        $search_column = [
            'id' => 'id',
            'user_id' => 'user_id',
            'item_gift_id' => 'item_gift_id',
        ];

        $sortable_and_searchable_column = [
            'search'        => $search,
            'search_column' => $search_column,
            'sort_column'   => array_merge($search, $search_column),
        ];
        
        return $this->repository->getIndexData($locale, $sortable_and_searchable_column);
    }

    public function wishlist($locale, $id, $data)
    {
        $item_gift = $this->item_gift_repository->getSingleData($locale, $id);
        $check_wishlist = $this->repository->getDataByUserAndItem($locale, $item_gift->id);

        DB::beginTransaction();
        if(!isset($check_wishlist)){
            Wishlist::create([
                'user_id' => auth()->user()->id,
                'item_gift_id' => $item_gift->id,
            ]);
            $response = response()->json([
                'message' => trans('all.success_add_to_wishlists'),
                'status' => 200,
                'error' => 0
            ]);
        } else {
            $check_wishlist->delete();
            $response = response()->json([
                'message' => trans('all.success_delete_from_wishlists'),
                'status' => 200,
                'error' => 0
            ]);
        }
        DB::commit();

        return $response;
    }
}
