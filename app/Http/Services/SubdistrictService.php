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

    public function index($locale, $data)
    {
        $search = [
            'city_id' => 'city_id',
            'name' => 'name',
        ];

        $searchColumn = [
            'id' => 'id',
            'city_id' => 'city_id',
            'name' => 'name',
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
