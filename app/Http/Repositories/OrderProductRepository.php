<?php

namespace App\Http\Repositories;

use App\Http\Models\OrderProduct;

class OrderProductRepository extends BaseRepository
{
    private $repository = 'Order Product';
    private $model;

	public function __construct(OrderProduct $model)
	{
		$this->model = $model;
	}

    public function getDataByOrderId($orderId)
    {
        return $this->model->with(['products', 'variants'])->getAll()->where('order_id', $orderId)->get();
    }
}
