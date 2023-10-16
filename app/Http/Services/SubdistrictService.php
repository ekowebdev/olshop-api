<?php

namespace App\Http\Services;

use App\Http\Models\Subdistrict;
use App\Http\Repositories\SubdistrictRepository;

class SubdistrictService extends BaseService
{
    private $model, $repository;

    public function __construct(Subdistrict $model, SubdistrictRepository $repository)
    {
        $this->model = $model;
        $this->repository = $repository;
    }

    public function getIndexData($locale, $data)
    {
        $search = [
            'city_id' => 'city_id',
            'subdistrict_name' => 'subdistrict_name',
        ];

        $search_column = [
            'subdistrict_id' => 'subdistrict_id',
            'city_id' => 'city_id',
            'subdistrict_name' => 'subdistrict_name',
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