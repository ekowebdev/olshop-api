<?php

namespace App\Http\Services;

use App\Http\Models\Rating;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\RatingResource;
use App\Exceptions\ValidationException;
use Illuminate\Support\Facades\Request;
use App\Http\Repositories\RatingRepository;
use App\Http\Repositories\ItemGiftRepository;

class RatingService extends BaseService
{
    private $model, $repository, $item_gift_repository;
    
    public function __construct(Rating $model, RatingRepository $repository, ItemGiftRepository $item_gift_repository)
    {
        $this->model = $model;
        $this->repository = $repository;
        $this->item_gift_repository = $item_gift_repository;
    }

    public function getIndexData($locale, $data)
    {
        $search = [
            'review_text' => 'review_text',
            'review_rating' => 'review_rating',
            'review_date' => 'review_date',
        ];

        $search_column = [
            'id' => 'id',
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
        $check_rating = $this->repository->getDataByUserAndItem($locale, $item_gift->id);

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
