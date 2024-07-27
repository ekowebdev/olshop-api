<?php

namespace App\Http\Repositories;

use Illuminate\Support\Arr;
use App\Http\Models\Variant;
use App\Exceptions\DataEmptyException;
use Illuminate\Support\Facades\Request;

class VariantRepository extends BaseRepository
{
    private $repository = 'Variant';
    private $model;

	public function __construct(Variant $model)
	{
		$this->model = $model;
	}

    public function index($locale, array $sortableAndSearchableColumn)
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
		$result = $this->model->getAll()->where($this->model->KeyPrimaryTable, $id)->first();

		if($result === null) throw new DataEmptyException(trans('validation.attributes.data_not_exist', ['attr' => $this->repository], $locale));

        return $result;
	}

    public function getSingleDataBySlug($locale, $slug)
	{
		$result = $this->model->getAll()->where('slug', $slug)->first();

		if($result === null) throw new DataEmptyException(trans('validation.attributes.data_not_exist', ['attr' => $this->repository], $locale));

        return $result;
	}
}
