<?php

namespace App\Http\Repositories;

use Illuminate\Support\Arr;
use App\Http\Models\Order;
use App\Exceptions\DataEmptyException;
use Illuminate\Support\Facades\Request;

class OrderRepository extends BaseRepository
{
    private $repository = 'Order';
    private $model;

	public function __construct(Order $model)
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

    public function getDataByUser($locale, array $sortableAndSearchableColumn, $id)
    {
        $this->validate(Request::all(), [
            'per_page' => ['numeric']
        ]);

        $result = $this->model
                    ->getAll()
                    ->where('user_id', $id)
                    ->setSortableAndSearchableColumn($sortableAndSearchableColumn)
                    ->search()
                    ->sort()
                    ->orderByDesc('id')
                    ->paginate(Arr::get(Request::all(), 'per_page', 15));

        $result->sortableAndSearchableColumn = $sortableAndSearchableColumn;

        if($result->total() == 0) throw new DataEmptyException(trans('validation.attributes.data_not_exist', ['attr' => $this->repository], $locale));

        return $result;
    }

    public function getSingleDataStatusNotSuccess($locale, $id)
	{
		$result = $this->model->getAll()->where($this->model->KeyPrimaryTable, $id)->where('status', '!=', 'success')->first();

        if($result === null) throw new DataEmptyException(trans('validation.attributes.data_not_exist', ['attr' => $this->repository], $locale));

        return $result;
	}

    public function getDataByIdAndReceipt($userId, $resi)
	{
		$result = $this->model
                  ->getAll()
                  ->with('shippings')
                  ->where('user_id', $userId)
                  ->whereHas('shippings', function ($query) use ($resi) {
                    $query->where('resi', $resi);
                  })
                  ->first();

		return $result;
	}
}
