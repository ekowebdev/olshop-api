<?php

namespace App\Http\Services;

use App\Http\Models\PaymentLog;
use App\Http\Services\PaymentLogService;
use App\Http\Repositories\PaymentLogRepository;

class PaymentLogService extends BaseService
{
    private $model, $repository, $item_gift_repository;
    
    public function __construct(PaymentLog $model, PaymentLogRepository $repository)
    {
        $this->model = $model;
        $this->repository = $repository;
    }

    public function getIndexData($locale, $data)
    {
        $search = [
            'payment_type' => 'payment_type',
            'redeem_id' => 'redeem_id',
            'payment_status' => 'payment_status',
        ];

        $search_column = [
            'id' => 'id',
            'payment_type' => 'payment_type',
            'redeem_id' => 'user_id',
            'payment_status' => 'payment_status',
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
}
