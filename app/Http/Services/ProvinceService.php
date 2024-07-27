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

    public function index($locale, $data)
    {
        $search = [
            'name' => 'name',
        ];

        $searchColumn = [
            'id' => 'id',
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
