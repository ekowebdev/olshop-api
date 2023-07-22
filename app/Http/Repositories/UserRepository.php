<?php

namespace App\Http\Repositories;

use Illuminate\Support\Arr;
use App\Http\Models\User;
use App\Exceptions\DataEmptyException;
use Illuminate\Support\Facades\Request;

class UserRepository extends BaseRepository 
{
    public $repository_name = 'User';

	public function __construct()
	{
		$this->model = new User;
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

    public function getDataByUsername($locale, $username)
	{
		$result = $this->model
                  ->getAll()
                  ->where('username', $username)	
                  ->first();
		return $result;	
	}
}