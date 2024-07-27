<?php

namespace App\Http\Services;

use App\Http\Models\PaymentLog;
use App\Http\Services\PaymentLogService;
use App\Http\Repositories\PaymentLogRepository;

class PaymentLogService extends BaseService
{
    private $model, $repository;

    public function __construct(PaymentLog $model, PaymentLogRepository $repository)
    {
        $this->model = $model;
        $this->repository = $repository;
    }

    public function index($locale, $data)
    {
        $search = [
            'type' => 'type',
            'order_id' => 'order_id',
            'status' => 'status',
        ];

        $searchColumn = [
            'id' => 'id',
            'type' => 'type',
            'order_id' => 'order_id',
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
}
