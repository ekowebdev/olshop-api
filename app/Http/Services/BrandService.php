<?php

namespace App\Http\Services;

use App\Http\Models\Brand;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Repositories\BrandRepository;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

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
            'logo',
        ]);

        $this->repository->validate($data_request, [
            'name' => [
                'required',
                'unique:brands,name',
            ],
            'sort' => [
                'required',
                'integer',
                'unique:brands,sort',
            ],
            'logo' => [
                'required',
                'max:1000',
                'image',
                'mimes:jpg,png',
            ],
        ]);

        DB::beginTransaction();

        $file = Request::file('logo');

        $imageName = uploadImagesToCloudinary($file, 'brands');

        $data_request['slug'] = Str::slug($data_request['name']);

        $result = $this->model->create([
            'name' => $data_request['name'],
            'slug' => $data_request['slug'],
            'sort' => $data_request['sort'],
            'logo' => $imageName,
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
            'logo',
        ]);

        $this->repository->validate($data_request, [
            'name' => [
                'unique:brands,name,' . $id,
            ],
            'sort' => [
                'integer',
                'unique:brands,sort,' . $id,
            ],
            'logo' => [
                'max:1000',
                'image',
                'mimes:jpg,png',
            ],
        ]);

        DB::beginTransaction();
        if (isset($data_request['logo'])) {
            $file = Request::file('logo');

            if ($check_data->logo) {
                $folder = config('services.cloudinary.folder');
                $previousPublicId = explode('.', $check_data->logo)[0];
                Cloudinary::destroy("$folder/images/brands/$previousPublicId");
                Cloudinary::destroy("$folder/images/brands/thumbnails/{$previousPublicId}_thumb");
            }

            $imageName = uploadImagesToCloudinary($file, 'brands');

            $check_data->logo = $imageName;
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
        $folder = config('services.cloudinary.folder');
        $previousPublicId = explode('.', $check_data->logo)[0];

        Cloudinary::destroy("$folder/images/brands/$previousPublicId");
        Cloudinary::destroy("$folder/images/brands/thumbnails/{$previousPublicId}_thumb");

        $result = $check_data->delete();
        DB::commit();

        return $result;
    }
}
