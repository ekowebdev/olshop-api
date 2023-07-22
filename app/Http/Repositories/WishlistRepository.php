<?php

namespace App\Http\Repositories;

use Illuminate\Support\Arr;
use App\Http\Models\Wishlist;
use App\Exceptions\DataEmptyException;
use Illuminate\Support\Facades\Request;

class WishlistRepository extends BaseRepository 
{
    public $repository_name = 'Wishlist';

	public function __construct()
	{
		$this->model = new Wishlist;
	}

    public function getIndexData($locale, array $sortableAndSearchableColumn)
    {
        $this->validate(Request::all(), [
            'per_page' => ['numeric']
        ]);
        $result = $this->model
                    ->getAll()
                    ->setSortableAndSearchableColumn($sortableAndSearchableColumn)
                    ->search()
                    ->sort()
                    ->orderByDesc('id')
                    ->paginate(Arr::get(Request::all(), 'per_page', 15));
        $result->sortableAndSearchableColumn = $sortableAndSearchableColumn;
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

    public function getDataByUserAndItem($locale, $item_id)
	{
		$result = $this->model
                  ->getAll()
                  ->where('user_id', auth()->user()->id)	
                  ->where('item_gift_id', $item_id)	
                  ->first();
		return $result;	
	}
}