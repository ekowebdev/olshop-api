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

    public function index($locale, $data)
    {
        $search = [
            'province_id' => 'province_id',
            'name' => 'name',
            'postal_code' => 'postal_code',
        ];

        $searchColumn = [
            'id' => 'id',
            'province_id' => 'province_id',
            'name' => 'name',
            'postal_code' => 'postal_code',
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
