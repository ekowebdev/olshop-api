<?php

namespace App\Http\Services;

use App\Http\Models\Redeem;
use Illuminate\Support\Arr;
use App\Http\Models\Shipping;
use Illuminate\Support\Facades\DB;
use App\Exceptions\ValidationException;
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
            'service' => 'service',
            'resi' => 'resi',
            'status' => 'status',
        ];

        $search_column = [
            'id' => 'id',
            'origin' => 'origin',
            'destination' => 'destination',
            'courier' => 'courier',
            'service' => 'service',
            'resi' => 'resi',
            'status' => 'status',
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

    public function update($locale, $id, $data)
    {
        $check_data = $this->repository->getSingleData($locale, $id);

        $data_request = Arr::only($data, [
            'resi',
            'status',
        ]);

        $this->repository->validate($data_request, [
            'resi' => [
                'string',
                'unique:shippings,resi,'.$id
            ],
        ]);

        DB::beginTransaction();
        if($check_data->redeems->redeem_status != 'shipped' || $check_data->redeems->payment_logs->payment_status != 'settlement'){
            throw new ValidationException(json_encode(['redeem_status' => [trans('error.redeem_not_completed', ['id' => $check_data->redeem_id])]]));
        }
        $data_request['resi'] = isset($data_request['resi']) ? $data_request['resi'] : $check_data->resi;
        $data_request['status'] = isset($data_request['resi']) ? 'on delivery' : $check_data->status;
        $check_data->update($data_request);
        DB::commit();

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
