<?php

namespace App\Http\Services;

use Image;
use App\Jobs\RedeemJob;
use App\Http\Models\Rating;
use App\Http\Models\Redeem;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Http\Models\ItemGift;
use App\Http\Models\Wishlist;
use Illuminate\Support\Facades\DB;
use App\Http\Models\RedeemItemGift;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\RatingResource;
use App\Http\Resources\RedeemResource;
use App\Exceptions\ValidationException;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\WishlistResource;
use App\Http\Repositories\RatingRepository;
use App\Http\Repositories\ItemGiftRepository;
use App\Http\Repositories\WishlistRepository;

class ItemGiftService extends BaseService
{
    private $model, $repository, $wishlist_repository, $rating_repository;
    
    public function __construct(ItemGift $model, ItemGiftRepository $repository, WishlistRepository $wishlist_repository, RatingRepository $rating_repository)
    {
        $this->model = $model;
        $this->repository = $repository;
        $this->wishlist_repository = $wishlist_repository;
        $this->rating_repository = $rating_repository;
    }

    public function getIndexData($locale, $data)
    {
        $search = [
            'item_gift_name' => 'item_gift_name',
            'item_gift_quantity' => 'item_gift_quantity',
            'item_gift_point' => 'item_gift_point',
            'total_rating' => 'total_rating',
        ];

        $search_column = [
            'id' => 'id',
            'item_gift_name' => 'item_gift_name',
            'item_gift_quantity' => 'item_gift_quantity',
            'item_gift_point' => 'item_gift_point',
            'total_rating' => 'total_rating',
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
            'item_gift_name',
            'item_gift_description',
            'item_gift_point',
            'item_gift_quantity',
            'item_gift_images',
        ]);

        $this->repository->validate($data_request, [
                'item_gift_name' => [
                    'required'
                ],
                'item_gift_description' => [
                    'required'
                ],
                'item_gift_point' => [
                    'required',
                    'numeric'
                ],
                'item_gift_quantity' => [
                    'required',
                    'numeric'
                ],
                'item_gift_images.*' => [
                    'max:10000',
                    'mimes:jpg,png'
                ],
            ]
        );

        DB::beginTransaction();
        $data_request['item_gift_code'] = Str::random(15);
        $result = $this->model->create($data_request);
        if (isset($data_request['item_gift_images'])) {
            foreach ($data_request['item_gift_images'] as $image) {
                $image_name = time() . '.' . $image->getClientOriginalExtension();
                Storage::disk('s3')->put('images/' . $image_name, file_get_contents($image));
                $img = Image::make($image);
                $img_thumb = $img->crop(5, 5);
                $img_thumb = $img_thumb->stream()->detach();
                Storage::disk('s3')->put('images/thumbnails/' . $image_name, $img_thumb);
                $result->item_gift_images()->create([
                    'item_gift_image' => $image_name,
                ]);
            }
        }
        DB::commit();

        return $this->repository->getSingleData($locale, $result->id);
    }

    public function update($locale, $id, $data)
    {
        $check_data = $this->repository->getSingleData($locale, $id);

        $data = array_merge([
            'item_gift_code' => $check_data->item_gift_code,
            'item_gift_name' => $check_data->item_gift_name,
            'item_gift_description' => $check_data->item_gift_description,
            'item_gift_point' => $check_data->item_gift_point,
            'item_gift_quantity' => $check_data->item_gift_quantity,
        ], $data);

        $data_request = Arr::only($data, [
            'item_gift_name',
            'item_gift_description',
            'item_gift_point',
            'item_gift_quantity',
        ]);

        $this->repository->validate($data_request, [
                'item_gift_name' => [
                    'required'
                ],
                'item_gift_description' => [
                    'required'
                ],
                'item_gift_point' => [
                    'required',
                    'numeric'
                ],
                'item_gift_quantity' => [
                    'required',
                    'numeric'
                ]
            ]
        );

        DB::beginTransaction();
        $check_data->update($data_request);
        DB::commit();

        return $this->repository->getSingleData($locale, $id);
    }

    public function delete($locale, $id)
    {
        $check_data = $this->repository->getSingleData($locale, $id);
        
        DB::beginTransaction();
        foreach($check_data->item_gift_images as $image) {
            if(Storage::disk('s3')->exists('images/' . $image->item_gift_image)) {
                Storage::disk('s3')->delete('images/' . $image->item_gift_image);
            }
            if(Storage::disk('s3')->exists('images/' . 'thumbnails/' . $image->item_gift_image)) {
                Storage::disk('s3')->delete('images/' . 'thumbnails/' . $image->item_gift_image);
            }
        }
        $result = $check_data->delete();
        DB::commit();

        return $result;
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

    public function wishlist($locale, $id, $data)
    {
        $item_gift = $this->repository->getSingleData($locale, $id);
        $check_wishlist = $this->wishlist_repository->getDataByUserAndItem($locale, $item_gift->id);

        DB::beginTransaction();
        if(!isset($check_wishlist)){
            $wishlist = Wishlist::create([
                'user_id' => auth()->user()->id,
                'item_gift_id' => $item_gift->id,
            ]);
            $response = response()->json([
                'message' => trans('all.success_add_to_wishlists'),
                'data' => new WishlistResource($wishlist),
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

    public function rating($locale, $id, $data)
    {
        $data_request = Arr::only($data, [
            'review_text',
            'review_rating',
        ]);

        $this->repository->validate($data_request, [
                'review_text' => [
                    'required'
                ],
                'review_rating' => [
                    'required',
                    'numeric',
                    'between:0.5,5'
                ],
            ]
        );

        $item_gift = $this->repository->getSingleData($locale, $id);
        $check_rating = $this->rating_repository->getDataByUserAndItem($locale, $item_gift->id);

        DB::beginTransaction();
        if(!isset($check_rating)){
            $rating = Rating::create([
                'user_id' => auth()->user()->id,
                'item_gift_id' => $item_gift->id,
                'review_text' => $data_request['review_text'],
                'review_rating' => calculate_rating($data_request['review_rating']),
                'review_date' => date('Y-m-d'),
            ]);
        } else {
            DB::rollback();
            throw new ValidationException(json_encode(['item_gift_id' => [trans('error.already_reviews', ['id' => $item_gift->id])]])); 
        }
        DB::commit();

        return response()->json([
            'message' => trans('all.success_reviews'),
            'status' => 200,
            'error' => 0
        ]);
    }
}
