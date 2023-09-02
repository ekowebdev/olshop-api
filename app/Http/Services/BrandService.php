<?php

namespace App\Http\Services;

use App\Http\Models\Brand;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Http\Repositories\BrandRepository;

class BrandService extends BaseService
{
    private $model, $repository;

    public function __construct(Brand $model, BrandRepository $repository)
    {
        $this->model = $model;
        $this->repository = $repository;
    }

    public function getIndexData($locale, $data)
    {
        $search = [
            'brand_name' => 'brand_name',
            'brand_slug' => 'brand_slug',
            'brand_sort' => 'brand_sort',
        ];

        $search_column = [
            'id' => 'id',
            'brand_name' => 'brand_name',
            'brand_slug' => 'brand_slug',
            'brand_sort' => 'brand_sort',
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

    public function store($locale, $data)
    {
        $data_request = Arr::only($data, [
            'brand_name',
            'brand_sort',
        ]);

        $this->repository->validate($data_request, [
                'brand_name' => [
                    'required',
                    'unique:brands,brand_name',
                ],
                'brand_sort' => [
                    'required',
                    'integer',
                    'unique:brands,brand_sort',
                ],
            ]
        );

        DB::beginTransaction();
        $data_request['brand_slug'] = Str::slug($data_request['brand_name']);
        $result = $this->model->create($data_request);
        DB::commit();

        return $this->repository->getSingleData($locale, $result->id);
    }

    public function update($locale, $id, $data)
    {
        $check_data = $this->repository->getSingleData($locale, $id);

        $data = array_merge([
            'brand_name' => $check_data->brand_name,
            'brand_sort' => $check_data->brand_sort,
        ], $data);

        $data_request = Arr::only($data, [
            'brand_name',
            'brand_sort',
        ]);

        $this->repository->validate($data_request, [
            'brand_name' => [
                'required',
                'unique:brands,brand_name',
            ],
            'brand_sort' => [
                'required',
                'integer',
                'unique:brands,brand_sort,' . $id,
            ],
        ]);

        DB::beginTransaction();
        $data_request['brand_slug'] = Str::slug($data_request['brand_name']);
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