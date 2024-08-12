<?php

namespace App\Http\Services;

use Illuminate\Support\Arr;
use App\Http\Models\Shipping;
use Illuminate\Support\Facades\DB;
use App\Exceptions\ApplicationException;
use App\Http\Repositories\ShippingRepository;

class ShippingService extends BaseService
{
    private $model, $repository;

    public function __construct(Shipping $model, ShippingRepository $repository)
    {
        $this->model = $model;
        $this->repository = $repository;
    }

    public function index($locale, $data)
    {
        $search = [
            'origin' => 'origin',
            'destination' => 'destination',
            'courier' => 'courier',
            'service' => 'service',
            'resi' => 'resi',
            'status' => 'status',
        ];

        $searchColumn = [
            'id' => 'id',
            'origin' => 'origin',
            'destination' => 'destination',
            'courier' => 'courier',
            'service' => 'service',
            'resi' => 'resi',
            'status' => 'status',
        ];

        $sortableAndSearchableColumn = [
            'search'        => $search,
            'search_column' => $searchColumn,
            'sort_column'   => array_merge($search, $searchColumn),
        ];

        return $this->repository->getAllData($locale, $sortableAndSearchableColumn);
    }

    public function show($locale, $id)
    {
        return $this->repository->getSingleData($locale, $id);
    }

    public function update($locale, $id, $data)
    {
        $checkData = $this->repository->getSingleData($locale, $id);

        $request = Arr::only($data, [
            'resi',
            'status',
        ]);

        $this->repository->validate($request, [
            'resi' => [
                'string',
                'unique:shippings,resi,'.$id
            ],
        ]);

        DB::beginTransaction();

        if($checkData->orders->status != 'shipped' && $checkData->orders->status != 'success' && $checkData->orders->payment_logs == null) throw new ApplicationException(trans('error.order_not_completed', ['order_code' => $checkData->orders->code]));

        $request['resi'] = isset($request['resi']) ? $request['resi'] : $checkData->resi;
        $request['status'] = isset($request['resi']) ? 'on delivery' : $checkData->status;
        $checkData->update($request);

        DB::commit();

        return $this->repository->getSingleData($locale, $id);
    }

    public function delete($locale, $id)
    {
        $checkData = $this->repository->getSingleData($locale, $id);

        DB::beginTransaction();

        $result = $checkData->delete();

        DB::commit();

        return $result;
    }
}
