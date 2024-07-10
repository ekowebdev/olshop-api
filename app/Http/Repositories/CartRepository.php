<?php

namespace App\Http\Repositories;

use App\Http\Models\Cart;
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
        $per_page = (int) Request::get('per_page', 10);
        $page = (int) Request::get('page', 1);

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
                    ->where('id', $id)
                    ->first();
		if($result === null) throw new DataEmptyException(trans('validation.attributes.data_not_exist', ['attr' => $this->repository_name], $locale));
        return $result;
	}

    public function getDataByUser($locale, $user_id)
	{
        $user_id = (int) $user_id;
        $per_page = (int) Request::get('per_page', 10);
        $page = (int) Request::get('page', 1);

		$data = $this->model
                    ->query()
                    ->where('user_id', $user_id)
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

    public function getByUserProductAndVariant($user_id, $product_id, $variant_id)
	{
        $variant_id = ($variant_id == null) ? '' : (int) $variant_id;
		$result = $this->model
                  ->where('user_id', '=', (int) $user_id)
                  ->where('product_id', '=', (int) $product_id)
                  ->where('variant_id', '=', $variant_id);
		return $result;
	}
}
