<?php

namespace App\Http\Repositories;

use Illuminate\Support\Arr;
use App\Http\Models\ItemGift;
use App\Exceptions\DataEmptyException;
use Illuminate\Support\Facades\Request;

class ItemGiftRepository extends BaseRepository 
{
    private $repository_name = 'Item Gift';
    private $model;

	public function __construct(ItemGift $model)
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
                  ->where($this->model->KeyPrimaryTable, $id)	
                  ->first();
		if($result === null) throw new DataEmptyException(trans('validation.attributes.data_not_exist', ['attr' => $this->repository_name], $locale));
        return $result;	
	}

    public function getSingleDataBySlug($locale, $slug)
	{
		$result = $this->model
                  ->getAll()
                  ->where('item_gift_slug', $slug)	
                  ->first();
		if($result === null) throw new DataEmptyException(trans('validation.attributes.data_not_exist', ['attr' => $this->repository_name], $locale));
        return $result;	
	}

    public function getSingleDataByCategory($locale, array $sortable_and_searchable_column, $category)
	{
        $this->validate(Request::all(), [
            'per_page' => ['numeric']
        ]);
		$result = $this->model
                ->getAll()
                ->whereHas('category', function ($query) use ($category) {
                    $query->where('category_slug', $category);
                })
                ->setSortableAndSearchableColumn($sortable_and_searchable_column)
                ->search()
                ->sort()
                ->orderByDesc('id')
                ->paginate(Arr::get(Request::all(), 'per_page', 15));
        $result->sortableAndSearchableColumn = $sortable_and_searchable_column;
        if($result->total() == 0) throw new DataEmptyException(trans('validation.attributes.data_not_exist', ['attr' => $this->repository_name], $locale));
        return $result;
	}

    public function getDataByBrand($locale, array $sortable_and_searchable_column, $brand)
	{
		$this->validate(Request::all(), [
            'per_page' => ['numeric']
        ]);
		$result = $this->model
                ->getAll()
                ->whereHas('brand', function ($query) use ($brand) {
                    $query->where('brand_slug', $brand);
                })
                ->setSortableAndSearchableColumn($sortable_and_searchable_column)
                ->search()
                ->sort()
                ->orderByDesc('id')
                ->paginate(Arr::get(Request::all(), 'per_page', 15));
        $result->sortableAndSearchableColumn = $sortable_and_searchable_column;
        if($result->total() == 0) throw new DataEmptyException(trans('validation.attributes.data_not_exist', ['attr' => $this->repository_name], $locale));
        return $result;	
	}
}