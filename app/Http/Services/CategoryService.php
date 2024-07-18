<?php

namespace App\Http\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Http\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Repositories\CategoryRepository;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

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
            'name' => 'name',
            'slug' => 'slug',
            'sort' => 'sort',
        ];

        $search_column = [
            'id' => 'id',
            'name' => 'name',
            'slug' => 'slug',
            'sort' => 'sort',
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
            'name',
            'sort',
            'image',
        ]);

        $this->repository->validate($data_request, [
            'name' => [
                'required',
                'unique:categories,name',
            ],
            'sort' => [
                'required',
                'integer',
                'unique:categories,sort',
            ],
            'image' => [
                'required',
                'max:1000',
                'image',
                'mimes:jpg,png',
            ],
        ]);

        DB::beginTransaction();

        $file = Request::file('image');

        $imageName = uploadImagesToCloudinary($file, 'categories');

        $data_request['code'] = (string) Str::uuid();
        $data_request['slug'] = Str::slug($data_request['name']);

        $result = $this->model->create([
            'code' => $data_request['code'],
            'name' => $data_request['name'],
            'slug' => $data_request['slug'],
            'sort' => $data_request['sort'],
            'image' => $imageName,
        ]);
        DB::commit();

        return $this->repository->getSingleData($locale, $result->id);
    }

    public function update($locale, $id, $data)
    {
        $check_data = $this->repository->getSingleData($locale, $id);

        $data_request = Arr::only($data, [
            'name',
            'sort',
            'image',
        ]);

        $this->repository->validate($data_request, [
            'name' => [
                'unique:categories,name,' . $id,
            ],
            'sort' => [
                'integer',
                'unique:categories,sort,' . $id,
            ],
            'image' => [
                'max:1000',
                'image',
                'mimes:jpg,png',
            ],
        ]);

        DB::beginTransaction();

        if (isset($data_request['image'])) {
            $file = Request::file('image');

            if ($check_data->image) {
                deleteImagesFromCloudinary($check_data->image, 'categories');
            }

            $imageName = uploadImagesToCloudinary($file, 'categories');

            $check_data->image = $imageName;
        }

        $data_request['slug'] = Str::slug($data_request['name'] ?? $check_data->name);

        $check_data->name = $data_request['name'] ?? $check_data->name;
        $check_data->slug = $data_request['slug'] ?? $check_data->slug;
        $check_data->sort = $data_request['sort'] ?? $check_data->sort;
        $check_data->save();

        DB::commit();

        return $this->repository->getSingleData($locale, $id);
    }

    public function delete($locale, $id)
    {
        $check_data = $this->repository->getSingleData($locale, $id);

        DB::beginTransaction();
        deleteImagesFromCloudinary($check_data->image, 'categories');
        $result = $check_data->delete();
        DB::commit();

        return $result;
    }
}
