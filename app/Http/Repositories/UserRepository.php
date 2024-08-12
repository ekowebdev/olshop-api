<?php

namespace App\Http\Repositories;

use Illuminate\Support\Arr;
use App\Http\Models\User;
use App\Exceptions\DataEmptyException;
use Illuminate\Support\Facades\Request;

class UserRepository extends BaseRepository
{
    private $repository = 'User';
    private $model;

	public function __construct(User $model)
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

    public function getSingleDataByMultipleParam(array $params)
	{
        $query = $this->model->getAll();

        foreach ($params as $key => $param) {
            $query->where($key, $param);
        }

        $result = $query->first();

		return $result;
	}
}
