<?php

namespace App\Http\Repositories;

use Illuminate\Support\Arr;
use App\Http\Models\Redeem;
use App\Exceptions\DataEmptyException;
use Illuminate\Support\Facades\Request;

class RedeemRepository extends BaseRepository 
{
    private $repository_name = 'Redeem';
    private $model;

	public function __construct(Redeem $model)
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

    public function getSingleDataStatusNotSuccess($locale, $id)
	{
		$result = $this->model
                  ->getAll()
                  ->where($this->model->KeyPrimaryTable, $id)
                  ->where('redeem_status', '!=', 'success')	
                  ->first();
		if($result === null) throw new DataEmptyException(trans('validation.attributes.data_not_exist', ['attr' => $this->repository_name], $locale));
        return $result;	
	}
}