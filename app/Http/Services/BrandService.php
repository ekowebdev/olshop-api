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

    public function index($locale, $data)
    {
        $search = [
            'name' => 'name',
            'slug' => 'slug',
            'sort' => 'sort',
        ];

        $searchColumn = [
            'id' => 'id',
            'name' => 'name',
            'slug' => 'slug',
            'sort' => 'sort',
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

    public function showBySlug($locale, $slug)
    {
        return $this->repository->getSingleDataBySlug($locale, $slug);
    }

    public function store($locale, $data)
    {
        $request = Arr::only($data, [
            'name',
            'sort',
            'logo',
        ]);

        $this->repository->validate($request, [
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

        $request['slug'] = Str::slug($request['name']);

        $result = $this->model->create([
            'name' => $request['name'],
            'slug' => $request['slug'],
            'sort' => $request['sort'],
            'logo' => $imageName,
        ]);

        DB::commit();

        return $this->repository->getSingleData($locale, $result->id);
    }

    public function update($locale, $id, $data)
    {
        $checkData = $this->repository->getSingleData($locale, $id);

        $request = Arr::only($data, [
            'name',
            'sort',
            'logo',
        ]);

        $this->repository->validate($request, [
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

        if (isset($request['logo'])) {
            $file = Request::file('logo');

            if ($checkData->logo) {
                deleteImagesFromCloudinary($checkData->logo, 'brands');
            }

            $imageName = uploadImagesToCloudinary($file, 'brands');

            $checkData->logo = $imageName;
        }

        $request['slug'] = Str::slug($request['name'] ?? $checkData->name);

        $checkData->name = $request['name'] ?? $checkData->name;
        $checkData->slug = $request['slug'] ?? $checkData->slug;
        $checkData->sort = $request['sort'] ?? $checkData->sort;
        $checkData->save();

        DB::commit();

        return $this->repository->getSingleData($locale, $id);
    }

    public function delete($locale, $id)
    {
        $checkData = $this->repository->getSingleData($locale, $id);

        DB::beginTransaction();

        deleteImagesFromCloudinary($checkData->logo, 'brands');

        $result = $checkData->delete();

        DB::commit();

        return $result;
    }
}
