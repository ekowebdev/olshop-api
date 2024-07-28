<?php

namespace App\Http\Repositories;

use Illuminate\Support\Arr;
use App\Http\Models\Address;
use App\Exceptions\DataEmptyException;
use Illuminate\Support\Facades\Request;

class AddressRepository extends BaseRepository
{
    private $repository = 'Address';
    private $model;

	public function __construct(Address $model)
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
                    ->orderByDesc('addresses.id')
                    ->paginate(Arr::get(Request::all(), 'per_page', 15));

        $result->sortableAndSearchableColumn = $sortableAndSearchableColumn;

        if($result->total() == 0) throw new DataEmptyException(trans('validation.attributes.data_not_exist', ['attr' => $this->repository], $locale));

        return $result;
    }

	public function getSingleData($locale, $id)
	{
		$result = $this->model->getAll()->where('addresses.id', $id)->first();

		if($result === null) throw new DataEmptyException(trans('validation.attributes.data_not_exist', ['attr' => $this->repository], $locale));

        return $result;
	}

    public function countDataByUser($userId)
	{
		$result = $this->model->getAll()->where('addresses.user_id', $userId)->get()->count();

        return $result;
	}
}
