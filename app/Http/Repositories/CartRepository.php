<?php

namespace App\Http\Repositories;

use App\Http\Models\Cart;
use Aws\DynamoDb\Marshaler;
use Illuminate\Support\Arr;
use App\Exceptions\DataEmptyException;
use Illuminate\Support\Facades\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class CartRepository extends BaseRepository 
{
    private $repository_name = 'Cart';
    private $model;

	public function __construct(Cart $model)
	{
		$this->model = $model;
	}

    public function getIndexData($locale)
    {
        $per_page = intval(Request::get('per_page', 10));
        $page = intval(Request::get('page', 1));

        $data = $this->model
            ->query()
            ->orderBy('created_at', 'desc')
            ->get();

        if ($data->isEmpty()) {
            throw new DataEmptyException(trans('validation.attributes.data_not_exist', ['attr' => $this->repository_name], $locale));
        }

        $result = new LengthAwarePaginator(
            $data->forPage($page, $per_page),
            $data->count(),
            $per_page,
            $page,
            ['path' => url('/carts')]
        );

        if ($result->isEmpty()) {
            throw new DataEmptyException(trans('validation.attributes.data_not_exist', ['attr' => $this->repository_name], $locale));
        }

        return $result;
    }

	public function getSingleData($locale, $id)
	{
		$result = $this->model
                    ->all()
                    ->where('id', $id)	
                    ->first();
		if($result === null) throw new DataEmptyException(trans('validation.attributes.data_not_exist', ['attr' => $this->repository_name], $locale));
        return $result;		
	}

    public function getDataByUser($locale, $user_id)
	{
        $user_id = intval($user_id);
        $per_page = intval(Request::get('per_page', 10));
        $page = intval(Request::get('page', 1));

		$data = $this->model
                    ->query()
                    ->orderBy('created_at', 'desc')
                    ->where('user_id', $user_id)
                    ->get();

		if ($data->isEmpty()) {
            throw new DataEmptyException(trans('validation.attributes.data_not_exist', ['attr' => $this->repository_name], $locale));
        }

        $result = new LengthAwarePaginator(
            $data->forPage($page, $per_page),
            $data->count(),
            $per_page,
            $page,
            ['path' => url('/carts')]
        );

        if ($result->isEmpty()) {
            throw new DataEmptyException(trans('validation.attributes.data_not_exist', ['attr' => $this->repository_name], $locale));
        }

        return $result;
	}

    public function getByItemAndVariant($item_gift_id, $variant_id)
	{
        $variant_id = ($variant_id == null) ? null : intval($variant_id);
		$result = $this->model
                  ->all()
                  ->where('item_gift_id', intval($item_gift_id))
                  ->where('variant_id', '=', $variant_id);
		return $result;	
	}
}