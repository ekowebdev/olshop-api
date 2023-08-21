<?php

namespace App\Http\Repositories;

use App\Http\Models\ItemGiftImage;
use App\Exceptions\DataEmptyException;

class ItemGiftImageRepository extends BaseRepository 
{
    private $repository_name = 'Item Gift Image';
    private $model;

	public function __construct(ItemGiftImage $model)
	{
		$this->model = $model;
	}

	public function getSingleData($locale, $id, $image_name)
	{
		$result = $this->model
                  ->getAll()
                  ->where('item_gift_id', $id)
				  ->where('item_gift_image', $image_name)	
                  ->first();
		if($result === null) throw new DataEmptyException(trans('validation.attributes.data_not_exist', ['attr' => $this->repository_name], $locale));
        return $result;	
	}
}