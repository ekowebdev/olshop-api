<?php

namespace App\Http\Services;

use App\Http\Models\Province;
use App\Http\Repositories\ProvinceRepository;

class ProvinceService extends BaseService
{
    private $model, $repository;

    public function __construct(Province $model, ProvinceRepository $repository)
    {
        $this->model = $model;
        $this->repository = $repository;
    }

    public function getIndexData($locale, $data)
    {
        $search = [
            'province_name' => 'province_name',
        ];

        $search_column = [
            'province_id' => 'province_id',
            'province_name' => 'province_name',
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