<?php

namespace App\Http\Services;

use App\Http\Models\Review;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use App\Exceptions\ValidationException;
use App\Http\Repositories\ReviewRepository;
use App\Http\Repositories\ItemGiftRepository;

class ReviewService extends BaseService
{
    private $model, $repository, $item_gift_repository;
    
    public function __construct(Review $model, ReviewRepository $repository, ItemGiftRepository $item_gift_repository)
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
            'review_text' => 'review_text',
            'review_rating' => 'review_rating',
            'review_date' => 'review_date',
        ];

        $search_column = [
            'id' => 'id',
            'user_id' => 'user_id',
            'item_gift_id' => 'item_gift_id',
            'review_text' => 'review_text',
            'review_rating' => 'review_rating',
            'review_date' => 'review_date',
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

    public function rating($locale, $id, $data)
    {
        $data_request = Arr::only($data, [
            'review_text',
            'review_rating',
        ]);

        $this->item_gift_repository->validate($data_request, [
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

        $item_gift = $this->item_gift_repository->getSingleData($locale, $id);
        $user = auth()->user();
        $check_rating = $this->repository->getDataByUserAndItem($locale, $item_gift->id);

        DB::beginTransaction();

        if (isset($check_rating)) {
            return response()->json([
                'message' => trans('error.already_reviews', ['id' => $item_gift->id]),
                'status' => 409,
            ], 409);
        }

        Review::create([
            'user_id' => $user->id,
            'item_gift_id' => $item_gift->id,
            'review_text' => $data_request['review_text'],
            'review_rating' => calculate_rating($data_request['review_rating']),
            'review_date' => date('Y-m-d'),
        ]);

        DB::commit();

        return response()->json([
            'message' => trans('all.success_reviews'),
            'status' => 200,
            'error' => 0,
        ]);
    }
}
