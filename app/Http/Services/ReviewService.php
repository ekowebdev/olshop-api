<?php

namespace App\Http\Services;

use App\Http\Models\Review;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use App\Exceptions\ValidationException;
use App\Http\Repositories\ReviewRepository;
use App\Http\Repositories\RedeemRepository;

class ReviewService extends BaseService
{
    private $model, $repository, $redeem_repository;
    
    public function __construct(Review $model, ReviewRepository $repository, RedeemRepository $redeem_repository)
    {
        $this->model = $model;
        $this->repository = $repository;
        $this->redeem_repository = $redeem_repository;
    }

    public function getIndexData($locale, $data)
    {
        $search = [
            'user_id' => 'user_id',
            'redeem_id' => 'redeem_id',
            'item_gift_id' => 'item_gift_id',
            'review_text' => 'review_text',
            'review_rating' => 'review_rating',
            'review_date' => 'review_date',
        ];

        $search_column = [
            'id' => 'id',
            'user_id' => 'user_id',
            'redeem_id' => 'redeem_id',
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

    public function review($locale, $data)
    {
        $data_request = Arr::only($data, [
            'redeem_id',
            'item_gift_id',
            'review_text',
            'review_rating',
        ]);

        $this->repository->validate($data_request, [
                'redeem_id' => [
                    'required',
                    'exists:redeems,id',
                ],
                'item_gift_id' => [
                    'required',
                    'exists:item_gifts,id',
                ],
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

        DB::beginTransaction();

        $user = auth()->user();
        $redeem = $this->redeem_repository->getSingleData($locale, $data_request['redeem_id']);

        if ($redeem->redeem_status != 'success') {
            return response()->json([
                'message' => trans('error.redeem_not_completed', ['id' => $data_request['redeem_id']]),
                'status' => 400,
            ], 400);
        }

        $check_rating = $this->repository->getDataByUserRedeemAndItem($locale, $data_request['redeem_id'], $data_request['item_gift_id']);

        if (isset($check_rating)) {
            return response()->json([
                'message' => trans('error.already_reviews', ['id' => $data_request['item_gift_id']]),
                'status' => 409,
            ], 409);
        }

        Review::create([
            'user_id' => $user->id,
            'redeem_id' => $data_request['redeem_id'],
            'item_gift_id' => $data_request['item_gift_id'],
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

    public function update($locale, $id, $data)
    {
        $check_data = $this->repository->getSingleData($locale, $id);

        $data = array_merge([
            'user_id' => $check_data->user_id,
            'redeem_id' => $check_data->redeem_id,
            'item_gift_id' => $check_data->item_gift_id,
            'review_text' => $check_data->review_text,
            'review_rating' => $check_data->review_rating,
        ], $data);

        $data_request = Arr::only($data, [
            'review_text',
            'review_rating',
        ]);

        $this->repository->validate($data_request, [
                'review_text' => [
                    'string'
                ],
                'review_rating' => [
                    'numeric',
                    'between:0.5,5'
                ],
            ]
        );

        DB::beginTransaction();
        $data_request['review_rating'] = calculate_rating($data_request['review_rating']);
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
