<?php

namespace App\Http\Services;

use App\Http\Models\Shipping;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use App\Http\Repositories\ShippingRepository;

class ShippingService extends BaseService
{
    private $model, $repository;
    
    public function __construct(Shipping $model, ShippingRepository $repository)
    {
        $this->model = $model;
        $this->repository = $repository;
    }

    public function getIndexData($locale, $data)
    {
        $search = [
            'origin' => 'origin',
            'destination' => 'destination',
            'courier' => 'courier',
            'cart_quantity' => 'cart_quantity',
        ];

        $search_column = [
            'id' => 'id',
            'origin' => 'origin',
            'destination' => 'destination',
            'courier' => 'courier',
            'service' => 'service',
        ];

        $sortable_and_searchable_column = [
            'search'        => $search,
            'search_column' => $search_column,
            'sort_column'   => array_merge($search, $search_column),
        ];
        
        return $this->repository->getIndexData($locale, $sortable_and_searchable_column);
    }

    public function getSingleData($locale, $id)
    {
        return $this->repository->getSingleData($locale, $id);
    }

    public function delete($locale, $id)
    {
        $check_data = $this->repository->getSingleData($locale, $id);
        
        DB::beginTransaction();
        $result = $check_data->delete();
        DB::commit();

        return $result;
    }
}
