<?php

namespace App\Http\Repositories;

use Illuminate\Support\Arr;
use App\Http\Models\ItemGiftImage;
use App\Exceptions\DataEmptyException;
use Illuminate\Support\Facades\Request;

class ItemGiftImageRepository extends BaseRepository 
{
    private $repository_name = 'Item Gift Image';
    private $model;

	public function __construct(ItemGiftImage $model)
	{
		$this->model = $model;
	}

	public function getSingleData($locale, $id)
	{
		$result = $this->model
                  ->getAll()
                  ->where('item_gift_id', $id)	
                  ->first();

		if($result === null) throw new DataEmptyException(trans('validation.attributes.data_not_exist', ['attr' => $this->repository_name], $locale));
		
        return $result;	
	}
}