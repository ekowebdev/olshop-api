<?php

namespace App\Http\Services;

use App\Http\Models\City;
use App\Http\Repositories\CityRepository;

class CityService extends BaseService
{
    private $model, $repository;

    public function __construct(City $model, CityRepository $repository)
    {
        $this->model = $model;
        $this->repository = $repository;
    }

    public function getIndexData($locale, $data)
    {
        $search = [
            'province_id' => 'province_id',
            'name' => 'name',
            'postal_code' => 'postal_code',
        ];

        $search_column = [
            'id' => 'id',
            'province_id' => 'province_id',
            'name' => 'name',
            'postal_code' => 'postal_code',
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