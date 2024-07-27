<?php

namespace App\Http\Repositories;

use Illuminate\Support\Arr;
use App\Http\Models\ProductImage;
use App\Exceptions\DataEmptyException;
use Illuminate\Support\Facades\Request;

class ProductImageRepository extends BaseRepository
{
    private $repository = 'Product Image';
    private $model;

	public function __construct(ProductImage $model)
	{
		$this->model = $model;
	}

	public function getAllData($locale, array $sortableAndSearchableColumn)
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

        if($result->total() == 0) throw new DataEmptyException(trans('validation.attributes.data_not_exist', ['attr' => $this->repository], $locale));

        return $result;
    }

	public function getSingleData($locale, $id)
	{
		$result = $this->model->getAll()->where('id', $id)->first();

		if($result === null) throw new DataEmptyException(trans('validation.attributes.data_not_exist', ['attr' => $this->repository], $locale));

        return $result;
	}

    public function getDataByProduct($locale, $productId)
	{
		$result = $this->model->getAll()->where('product_id', $producId)->get();

        return $result;
	}

    public function getSingleDataByProductVariant($locale, $producId, $variantId)
	{
		$result = $this->model->getAll()->where('product_id', $producId)->where('variant_id', $variantId)->first();

        return $result;
	}

    public function countDataByProduct($producId)
	{
		$result = $this->model->getAll()->where('product_id', $producId)->get()->count();

        return $result;
	}
}
