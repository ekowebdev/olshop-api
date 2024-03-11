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
            ]
        );

        DB::beginTransaction();
        $data_request['code'] = Str::uuid();
        $data_request['slug'] = Str::slug($data_request['name']);
        $image = $data_request['image'];
        $image_name = time() . '.' . $image->getClientOriginalExtension();
        Storage::disk('s3')->put('images/category/' . $image_name, file_get_contents($image));
        $img = Image::make($image);
        $img_thumb = $img->crop(5, 5);
        $img_thumb = $img_thumb->stream()->detach();
        Storage::disk('s3')->put('images/category/thumbnails/' . $image_name, $img_thumb);
        $result = $this->model->create([
            'code' => $data_request['code'],
            'name' => $data_request['name'],
            'slug' => $data_request['slug'],
            'sort' => $data_request['sort'],
            'image' => $image_name,
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
            if(Storage::disk('s3')->exists('images/category/' . $check_data->image)) Storage::disk('s3')->delete('images/category/' . $check_data->image);
            if(Storage::disk('s3')->exists('images/category/thumbnails/' . $check_data->image)) Storage::disk('s3')->delete('images/category/thumbnails/' . $check_data->image);
            $image = $data_request['image'];
            $image_name = time() . '.' . $image->getClientOriginalExtension();
            Storage::disk('s3')->put('images/category/' . $image_name, file_get_contents($image));
            $img = Image::make($image);
            $img_thumb = $img->crop(5, 5);
            $img_thumb = $img_thumb->stream()->detach();
            Storage::disk('s3')->put('images/category/thumbnails/' . $image_name, $img_thumb);
            $check_data->image = $image_name;
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
        if(Storage::disk('s3')->exists('images/category/' . $check_data->image)) Storage::disk('s3')->delete('images/category/' . $check_data->image);
        $result = $check_data->delete();
        DB::commit();

        return $result;
    }
}