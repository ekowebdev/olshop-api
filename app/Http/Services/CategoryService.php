<?php

namespace App\Http\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Http\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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

    public function getSingleDataBySlug($locale, $slug)
    {
        return $this->repository->getSingleDataBySlug($locale, $slug);
    }

    public function store($locale, $data)
    {
        $data_request = Arr::only($data, [
            'category_name',
            'category_sort',
            'category_image',
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
                'category_image' => [
                    'required',
                    'max:1000',
                    'image',
                    'mimes:jpg,png',
                ],
            ]
        );

        DB::beginTransaction();
        $data_request['category_code'] = Str::uuid();
        $data_request['category_slug'] = Str::slug($data_request['category_name']);
        $image = $data_request['category_image'];
        $image_name = time() . '.' . $image->getClientOriginalExtension();
        Storage::disk('s3')->put('images/category/' . $image_name, file_get_contents($image));
        $result = $this->model->create([
            'category_code' => $data_request['category_code'],
            'category_name' => $data_request['category_name'],
            'category_slug' => $data_request['category_slug'],
            'category_sort' => $data_request['category_sort'],
            'category_image' => $image_name,
        ]);
        DB::commit();

        return $this->repository->getSingleData($locale, $result->id);
    }

    public function update($locale, $id, $data)
    {
        $check_data = $this->repository->getSingleData($locale, $id);

        $data_request = Arr::only($data, [
            'category_name',
            'category_sort',
            'category_image',
        ]);

        $this->repository->validate($data_request, [
            'category_name' => [
                'unique:categories,category_name,' . $id,
            ],
            'category_sort' => [
                'integer',
                'unique:categories,category_sort,' . $id,
            ],
            'category_image' => [
                'max:1000',
                'image',
                'mimes:jpg,png',
            ],
        ]);

        DB::beginTransaction();
        if (isset($data_request['category_image'])) {
            if(Storage::disk('s3')->exists('images/category/' . $check_data->category_image)) {
                Storage::disk('s3')->delete('images/category/' . $check_data->category_image);
            }
            $image = $data_request['category_image'];
            $image_name = time() . '.' . $image->getClientOriginalExtension();
            Storage::disk('s3')->put('images/category/' . $image_name, file_get_contents($image));
            $check_data->category_image = $image_name;
        }
        $data_request['category_slug'] = Str::slug($data_request['category_name'] ?? $check_data->category_name);
        $check_data->category_name = $data_request['category_name'] ?? $check_data->category_name;
        $check_data->category_slug = $data_request['category_slug'] ?? $check_data->category_slug;
        $check_data->category_sort = $data_request['category_sort'] ?? $check_data->category_sort;
        $check_data->save();
        DB::commit();

        return $this->repository->getSingleData($locale, $id);
    }

    public function delete($locale, $id)
    {
        $check_data = $this->repository->getSingleData($locale, $id);
        DB::beginTransaction();
        if(Storage::disk('s3')->exists('images/category/' . $check_data->category_image)) {
            Storage::disk('s3')->delete('images/category/' . $check_data->category_image);
        }
        $result = $check_data->delete();
        DB::commit();

        return $result;
    }
}