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

    public function getDataByUserRedeemAndItem($locale, $redeem_id, $item_gift_id)
	{
		$result = $this->model
                  ->getAll()
                  ->where('user_id', auth()->user()->id)	
                  ->where('redeem_id', $redeem_id)	
                  ->where('item_gift_id', $item_gift_id)	
                  ->first();
		return $result;	
	}
}