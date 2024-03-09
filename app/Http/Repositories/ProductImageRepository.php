<?php

namespace App\Http\Repositories;

use Illuminate\Support\Arr;
use App\Http\Models\ProductImage;
use App\Exceptions\DataEmptyException;
use Illuminate\Support\Facades\Request;

class ProductImageRepository extends BaseRepository 
{
    private $repository_name = 'Product Image';
    private $model;

	public function __construct(ProductImage $model)
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

    public function getSingleDataByProductVariant($locale, $product_id, $variant_id)
	{
		$result = $this->model
                  ->getAll()
                  ->where('product_id', $product_id)	
                  ->where('variant_id', $variant_id)	
                  ->first();
		return $result;	
	}
}
