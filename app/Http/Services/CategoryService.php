<?php

namespace App\Http\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Http\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
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
            'image',
        ]);

        $this->repository->validate($request, [
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

        $request['code'] = (string) Str::uuid();
        $request['slug'] = Str::slug($request['name']);

        $result = $this->model->create([
            'code' => $request['code'],
            'name' => $request['name'],
            'slug' => $request['slug'],
            'sort' => $request['sort'],
            'image' => $imageName,
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
            'image',
        ]);

        $this->repository->validate($request, [
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

        if (isset($request['image'])) {
            $file = Request::file('image');

            if ($checkData->image) {
                deleteImagesFromCloudinary($checkData->image, 'categories');
            }

            $imageName = uploadImagesToCloudinary($file, 'categories');

            $checkData->image = $imageName;
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

        deleteImagesFromCloudinary($checkData->image, 'categories');

        $result = $checkData->delete();

        DB::commit();

        return $result;
    }
}
