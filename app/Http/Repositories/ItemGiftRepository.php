<?php

namespace App\Http\Repositories;

use Illuminate\Support\Arr;
use App\Http\Models\ItemGift;
use App\Exceptions\DataEmptyException;
use Illuminate\Support\Facades\Request;

class ItemGiftRepository extends BaseRepository 
{
    public $repository_name = 'Item Gift';

	public function __construct()
	{
		$this->model = new ItemGift;
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
                  ->where($this->model->KeyPrimaryTable, $id)	
                  ->first();

		if($result === null) throw new DataEmptyException(trans('validation.attributes.data_not_exist', ['attr' => $this->repository_name], $locale));
		
        return $result;	
	}
}