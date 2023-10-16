<?php

namespace App\Http\Services;

use App\Http\Models\Brand;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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

    public function getSingleDataBySlug($locale, $slug)
    {
        return $this->repository->getSingleDataBySlug($locale, $slug);
    }

    public function store($locale, $data)
    {
        $data_request = Arr::only($data, [
            'brand_name',
            'brand_sort',
            'brand_logo',
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
                'brand_logo' => [
                    'required',
                    'max:1000',
                    'image',
                    'mimes:jpg,png',
                ],
            ]
        );

        DB::beginTransaction();
        $data_request['brand_slug'] = Str::slug($data_request['brand_name']);
        $image = $data_request['brand_logo'];
        $image_name = time() . '.' . $image->getClientOriginalExtension();
        Storage::disk('s3')->put('images/brand/' . $image_name, file_get_contents($image));
        $result = $this->model->create([
            'brand_name' => $data_request['brand_name'],
            'brand_slug' => $data_request['brand_slug'],
            'brand_sort' => $data_request['brand_sort'],
            'brand_logo' => $image_name,
        ]);
        DB::commit();

        return $this->repository->getSingleData($locale, $result->id);
    }

    public function update($locale, $id, $data)
    {
        $check_data = $this->repository->getSingleData($locale, $id);

        $data_request = Arr::only($data, [
            'brand_name',
            'brand_sort',
            'brand_logo',
        ]);

        $this->repository->validate($data_request, [
            'brand_name' => [
                'unique:brands,brand_name',
            ],
            'brand_sort' => [
                'integer',
                'unique:brands,brand_sort,' . $id,
            ],
            'brand_logo' => [
                'max:1000',
                'image',
                'mimes:jpg,png',
            ],
        ]);

        DB::beginTransaction();
        if (isset($data_request['brand_logo'])) {
            if(Storage::disk('s3')->exists('images/brand/' . $check_data->brand_logo)) {
                Storage::disk('s3')->delete('images/brand/' . $check_data->brand_logo);
            }
            $image = $data_request['brand_logo'];
            $image_name = time() . '.' . $image->getClientOriginalExtension();
            Storage::disk('s3')->put('images/brand/' . $image_name, file_get_contents($image));
            $check_data->brand_logo = $image_name;
        }
        $data_request['brand_slug'] = Str::slug($data_request['brand_name'] ?? $check_data->brand_name);
        $check_data->brand_name = $data_request['brand_name'] ?? $check_data->brand_name;
        $check_data->brand_slug = $data_request['brand_slug'] ?? $check_data->brand_slug;
        $check_data->brand_sort = $data_request['brand_sort'] ?? $check_data->brand_sort;
        $check_data->save();
        DB::commit();

        return $this->repository->getSingleData($locale, $id);
    }

    public function delete($locale, $id)
    {
        $check_data = $this->repository->getSingleData($locale, $id);
        DB::beginTransaction();
        if(Storage::disk('s3')->exists('images/brand/' . $check_data->brand_logo)) {
            Storage::disk('s3')->delete('images/brand/' . $check_data->brand_logo);
        }
        $result = $check_data->delete();
        DB::commit();

        return $result;
    }
}