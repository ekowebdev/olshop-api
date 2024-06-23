<?php

namespace App\Http\Services;

use Image;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Http\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Repositories\ProductRepository;

class ProductService extends BaseService
{
    private $model, $repository;

    public function __construct(Product $model, ProductRepository $repository)
    {
        $this->model = $model;
        $this->repository = $repository;
    }

    public function getIndexData($locale, $data)
    {
        $search = [
            'name' => 'name',
            'category_id' => 'category_id',
            'brand_id' => 'brand_id',
            'slug' => 'slug',
            'weight' => 'weight',
            'quantity' => 'quantity',
            'point' => 'point',
            'total_review' => 'total_review',
            'total_rating' => 'total_rating',
            'total_order' => 'total_order',
        ];

        $search_column = [
            'id' => 'id',
            'name' => 'name',
            'category_id' => 'category_id',
            'brand_id' => 'brand_id',
            'slug' => 'slug',
            'quantity' => 'quantity',
            'point' => 'point',
            'weight' => 'weight',
            'total_review' => 'total_review',
            'total_rating' => 'total_rating',
            'total_order' => 'total_order',
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

    public function getDataByCategory($locale, $category)
    {
        $search = [
            'name' => 'name',
            'category_id' => 'category_id',
            'brand_id' => 'brand_id',
            'slug' => 'slug',
            'weight' => 'weight',
            'quantity' => 'quantity',
            'point' => 'point',
            'total_review' => 'total_review',
            'total_rating' => 'total_rating',
            'total_order' => 'total_order',
        ];

        $search_column = [
            'id' => 'id',
            'name' => 'name',
            'category_id' => 'category_id',
            'brand_id' => 'brand_id',
            'slug' => 'slug',
            'quantity' => 'quantity',
            'point' => 'point',
            'weight' => 'weight',
            'total_review' => 'total_review',
            'total_rating' => 'total_rating',
            'total_order' => 'total_order',
        ];

        $sortable_and_searchable_column = [
            'search'        => $search,
            'search_column' => $search_column,
            'sort_column'   => array_merge($search, $search_column),
        ];

        return $this->repository->getSingleDataByCategory($locale, $sortable_and_searchable_column, $category);
    }

    public function getDataByBrand($locale, $brand)
    {
        $search = [
            'name' => 'name',
            'category_id' => 'category_id',
            'brand_id' => 'brand_id',
            'slug' => 'slug',
            'weight' => 'weight',
            'quantity' => 'quantity',
            'point' => 'point',
            'total_review' => 'total_review',
            'total_rating' => 'total_rating',
            'total_order' => 'total_order',
        ];

        $search_column = [
            'id' => 'id',
            'name' => 'name',
            'category_id' => 'category_id',
            'brand_id' => 'brand_id',
            'slug' => 'slug',
            'quantity' => 'quantity',
            'point' => 'point',
            'weight' => 'weight',
            'total_review' => 'total_review',
            'total_rating' => 'total_rating',
            'total_order' => 'total_order',
        ];

        $sortable_and_searchable_column = [
            'search'        => $search,
            'search_column' => $search_column,
            'sort_column'   => array_merge($search, $search_column),
        ];

        return $this->repository->getDataByBrand($locale, $sortable_and_searchable_column, $brand);
    }

    public function getDataByUserRecomendation($locale)
    {
        $search = [
            'name' => 'name',
            'category_id' => 'category_id',
            'brand_id' => 'brand_id',
            'slug' => 'slug',
            'weight' => 'weight',
            'quantity' => 'quantity',
            'point' => 'point',
            'total_review' => 'total_review',
            'total_rating' => 'total_rating',
            'total_order' => 'total_order',
        ];

        $search_column = [
            'id' => 'id',
            'name' => 'name',
            'category_id' => 'category_id',
            'brand_id' => 'brand_id',
            'slug' => 'slug',
            'quantity' => 'quantity',
            'point' => 'point',
            'weight' => 'weight',
            'total_review' => 'total_review',
            'total_rating' => 'total_rating',
            'total_order' => 'total_order',
        ];

        $sortable_and_searchable_column = [
            'search'        => $search,
            'search_column' => $search_column,
            'sort_column'   => array_merge($search, $search_column),
        ];

        return $this->repository->getDataByUserRecomendation($locale, $sortable_and_searchable_column);
    }

    public function store($locale, $data)
    {
        $data_request = Arr::only($data, [
            'name',
            'category_id',
            'brand_id',
            'description',
            'point',
            'weight',
            'quantity',
            'images',
            'spesification',
        ]);

        $this->repository->validate($data_request, [
                'name' => [
                    'required',
                    'unique:products,name',
                ],
                'category_id' => [
                    'nullable',
                    'exists:categories,id',
                ],
                'brand_id' => [
                    'nullable',
                    'exists:brands,id',
                ],
                'description' => [
                    'required',
                    'string',
                ],
                'spesification' => [
                    'nullable',
                    'array',
                ],
                'spesification.*.key' => [
                    'string',
                    'required_with:spesification.*.value',
                ],
                'spesification.*.value' => [
                    'string',
                    'required_with:spesification.*.key',
                ],
                'point' => [
                    'nullable',
                    'numeric',
                ],
                'weight' => [
                    'nullable',
                    'numeric',
                ],
                'quantity' => [
                    'nullable',
                    'numeric'
                ],
                'images' => [
                    'required',
                    'array'
                ],
                'images.*' => [
                    'required',
                    'max:1000',
                    'mimes:jpg,png',
                ],
            ]
        );
        DB::beginTransaction();
        $data_request['code'] = Str::random(15);
        $data_request['slug'] = Str::slug($data_request['name']);
        $data_request['point'] = $data_request['point'] ?? null;
        $data_request['weight'] = $data_request['weight'] ?? null;
        $data_request['quantity'] = $data_request['quantity'] ?? null;
        $data_request['spesification'] = (isset($data_request['spesification'])) ? json_encode($data_request['spesification']) : null;
        $result = $this->model->create($data_request);
        foreach ($data_request['images'] as $image) {
            $image_name = time() . '.' . $image->getClientOriginalExtension();
            Storage::disk('google')->put('images/' . $image_name, file_get_contents($image));
            $img = Image::make($image);
            $img_thumb = $img->crop(5, 5);
            $img_thumb = $img_thumb->stream()->detach();
            Storage::disk('google')->put('images/thumbnails/' . $image_name, $img_thumb);
            $result->product_images()->create(['image' => $image_name]);
        }
        DB::commit();

        return $this->repository->getSingleData($locale, $result->id);
    }

    public function update($locale, $id, $data)
    {
        $check_data = $this->repository->getSingleData($locale, $id);

        $data = array_merge([
            'name' => $check_data->name,
            'category_id' => $check_data->category_id,
            'brand_id' => $check_data->brand_id,
            'slug' => $check_data->slug,
            'description' => $check_data->description,
            'point' => $check_data->point,
            'weight' => $check_data->weight,
            'quantity' => $check_data->quantity,
            'spesification' => json_decode($check_data->spesification),
        ], $data);

        $data_request = Arr::only($data, [
            'name',
            'category_id',
            'brand_id',
            'slug',
            'description',
            'point',
            'weight',
            'quantity',
            'spesification',
        ]);

        $this->repository->validate($data_request, [
                'name' => [
                    'string',
                    'unique:products,name,' . $id,
                ],
                'category_id' => [
                    'nullable',
                    'exists:categories,id',
                ],
                'brand_id' => [
                    'nullable',
                    'exists:brands,id',
                ],
                'description' => [
                    'string',
                ],
                'spesification' => [
                    'nullable',
                    'array',
                ],
                'spesification.*.key' => [
                    'string',
                    'required_with:spesification.*.value',
                ],
                'spesification.*.value' => [
                    'string',
                    'required_with:spesification.*.key',
                ],
                'point' => [
                    'nullable',
                    'numeric',
                ],
                'weight' => [
                    'nullable',
                    'numeric',
                ],
                'quantity' => [
                    'numeric',
                ],
            ]
        );

        DB::beginTransaction();
        $data_request['slug'] = Str::slug($data_request['name']);
        $data_request['point'] = ($check_data->variants->count() > 0) ? min($check_data->variants->pluck('variant_point')->toArray()) : $data_request['point'];
        $data_request['spesification'] = (isset($data_request['spesification'])) ? json_encode($data_request['spesification']) : null;
        $check_data->update($data_request);
        DB::commit();

        return $this->repository->getSingleData($locale, $id);
    }

    public function delete($locale, $id)
    {
        $check_data = $this->repository->getSingleData($locale, $id);

        DB::beginTransaction();
        foreach($check_data->images as $image) {
            if(Storage::disk('google')->exists('images/' . $image->image)) Storage::disk('google')->delete('images/' . $image->image);
            if(Storage::disk('google')->exists('images/' . 'thumbnails/' . $image->image)) Storage::disk('google')->delete('images/' . 'thumbnails/' . $image->image);
        }
        $result = $check_data->delete();
        DB::commit();

        return $result;
    }
}
