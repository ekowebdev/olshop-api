<?php

namespace App\Http\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Http\Models\Category;
use Illuminate\Support\Facades\DB;
use App\Http\Repositories\CategoryRepository;

class CategoryService extends BaseService
{
    private $model, $repository;

    public function __construct(Category $model, CategoryRepository $repository)
    {
        $this->model = $model;
        $this->repository = $repository;
    }

    public function getIndexData($locale, $data)
    {
        $search = [
            'category_name' => 'category_name',
            'category_slug' => 'category_slug',
            'category_sort' => 'category_sort',
        ];

        $search_column = [
            'id' => 'id',
            'category_name' => 'category_name',
            'category_slug' => 'category_slug',
            'category_sort' => 'category_sort',
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
            'category_name',
            'category_sort',
        ]);

        $this->repository->validate($data_request, [
                'category_name' => [
                    'required',
                    'unique:categories,category_name',
                ],
                'category_sort' => [
                    'required',
                    'integer',
                    'unique:categories,category_sort',
                ],
            ]
        );

        DB::beginTransaction();
        $data_request['category_code'] = Str::uuid();
        $data_request['category_slug'] = Str::slug($data_request['category_name']);
        $result = $this->model->create($data_request);
        DB::commit();

        return $this->repository->getSingleData($locale, $result->id);
    }

    public function update($locale, $id, $data)
    {
        $check_data = $this->repository->getSingleData($locale, $id);

        $data = array_merge([
            'category_name' => $check_data->category_name,
            'category_sort' => $check_data->category_sort,
        ], $data);

        $data_request = Arr::only($data, [
            'category_name',
            'category_sort',
        ]);

        $this->repository->validate($data_request, [
            'category_name' => [
                'required',
                'unique:categories,category_name',
            ],
            'category_sort' => [
                'required',
                'integer',
                'unique:categories,category_sort,' . $id,
            ],
        ]);

        DB::beginTransaction();
        $data_request['category_slug'] = Str::slug($data_request['category_name']);
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