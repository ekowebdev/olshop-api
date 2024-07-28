<?php

namespace App\Http\Repositories;

use App\Http\Models\Cart;
use App\Exceptions\DataEmptyException;
use Illuminate\Support\Facades\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class CartRepository extends BaseRepository
{
    private $repository = 'Cart';
    private $model;

	public function __construct(Cart $model)
	{
		$this->model = $model;
	}

    public function getAllData($locale)
    {
        $perPage = (int) Request::get('per_page', 10);
        $page = (int) Request::get('page', 1);

        $data = $this->model->query()->orderBy('created_at', 'desc')->get();

        if ($data->isEmpty()) {
            throw new DataEmptyException(trans('validation.attributes.data_not_exist', ['attr' => $this->repository], $locale));
        }

        $result = new LengthAwarePaginator(
            $data->forPage($page, $perPage),
            $data->count(),
            $perPage,
            $page,
            ['path' => url('/api/v1/' . $locale. '/carts')]
        );

        if ($result->isEmpty()) {
            throw new DataEmptyException(trans('validation.attributes.data_not_exist', ['attr' => $this->repository], $locale));
        }

        return $result;
    }

	public function getSingleData($locale, $id)
	{
		$result = $this->model->where('id', $id)->first();

		if($result === null) throw new DataEmptyException(trans('validation.attributes.data_not_exist', ['attr' => $this->repository], $locale));

        return $result;
	}

    public function getDataByUser($locale, $userId)
	{
        $userId = (int) $userId;
        $perPage = (int) Request::get('per_page', 10);
        $page = (int) Request::get('page', 1);

		$data = $this->model
                    ->query()
                    ->where('user_id', $userId)
                    ->orderBy('created_at', 'desc')
                    ->get();

		if ($data->isEmpty()) {
            throw new DataEmptyException(trans('validation.attributes.data_not_exist', ['attr' => $this->repository], $locale));
        }

        $result = new LengthAwarePaginator(
            $data->forPage($page, $perPage),
            $data->count(),
            $perPage,
            $page,
            ['path' => url('/api/v1/' . $locale. '/carts')]
        );

        if ($result->isEmpty()) {
            throw new DataEmptyException(trans('validation.attributes.data_not_exist', ['attr' => $this->repository], $locale));
        }

        return $result;
	}

    public function getDataByUserProductAndVariant($userId, $productId, $variantId)
	{
        $variantId = ($variantId == null) ? '' : (int) $variantId;

		$result = $this->model->where('user_id', '=', (int) $userId)->where('product_id', '=', (int) $productId)->where('variant_id', '=', $variantId);

		return $result;
	}
}
