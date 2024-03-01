<?php

namespace App\Http\Repositories;

use Illuminate\Support\Arr;
use App\Http\Models\Review;
use App\Exceptions\DataEmptyException;
use Illuminate\Support\Facades\Request;

class ReviewRepository extends BaseRepository 
{
    private $repository_name = 'Review';
    private $model;

	public function __construct(Review $model)
	{
		$this->model = $model;
	}

    public function getIndexData($locale, array $sortable_and_searchable_column)
    {
        $this->validate(Request::all(), [
            'per_page' => ['numeric']
        ]);
        $result = $this->model
                    ->getAll()
                    ->setSortableAndSearchableColumn($sortable_and_searchable_column)
                    ->search()
                    ->sort()
                    ->orderByDesc('id')
                    ->paginate(Arr::get(Request::all(), 'per_page', 15));
        $result->sortableAndSearchableColumn = $sortable_and_searchable_column;
        if($result->total() == 0) throw new DataEmptyException(trans('validation.attributes.data_not_exist', ['attr' => $this->repository_name], $locale));
        return $result;
    }

	public function getSingleData($locale, $id)
	{
		$result = $this->model
                  ->getAll()
                  ->where('id', $id)	
                  ->first();
		if($result === null) throw new DataEmptyException(trans('validation.attributes.data_not_exist', ['attr' => $this->repository_name], $locale));
        return $result;	
	}

    public function getDataByUserOrder($locale, $user_id, $order_id)
	{
		$result = $this->model
                  ->getAll()
                  ->where('reviews.user_id', $user_id)	
                  ->where('reviews.order_id', $order_id)	
                  ->first();
		return $result;	
	}

    public function getDataByUserOrderAndProduct($locale, $user_id, $order_id, $product_id)
	{
		$result = $this->model
                  ->getAll()
                  ->where('reviews.user_id', $user_id)	
                  ->where('reviews.order_id', $order_id)	
                  ->where('reviews.product_id', $product_id)	
                  ->first();
		return $result;	
	}
}