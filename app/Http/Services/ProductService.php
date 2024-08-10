<?php

namespace App\Http\Services;

use Meilisearch\Client;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Http\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Meilisearch\Contracts\SearchQuery;
use App\Http\Resources\DeletedResource;
use App\Http\Resources\ProductResource;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Repositories\ProductRepository;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class ProductService extends BaseService
{
    private $model, $repository;

    public function __construct(Product $model, ProductRepository $repository)
    {
        $this->model = $model;
        $this->repository = $repository;
    }

    public function index($locale, $data)
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

        $searchColumn = [
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

        $sortableAndSearchableColumn = [
            'search'        => $search,
            'search_column' => $searchColumn,
            'sort_column'   => array_merge($search, $searchColumn),
        ];

        $result = Cache::remember('products_all_' . time(), now()->addMinutes(1), function() use ($locale, $sortableAndSearchableColumn) {
            return $this->repository->getAllData($locale, $sortableAndSearchableColumn);
        });

        return (ProductResource::collection($result))
                ->additional([
                    'sortableAndSearchableColumn' => $result->sortableAndSearchableColumn
                ]);
    }

    public function show($locale, $id)
    {
        return new ProductResource($this->repository->getSingleData($locale, $id));
    }

    public function showBySlug($locale, $slug)
    {
        return new ProductResource($this->repository->getSingleDataBySlug($locale, $slug));
    }

    public function showByCategory($locale, $category)
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

        $searchColumn = [
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

        $sortableAndSearchableColumn = [
            'search'        => $search,
            'search_column' => $searchColumn,
            'sort_column'   => array_merge($search, $searchColumn),
        ];

        $result = Cache::remember('products_by_category_' . time(), now()->addMinutes(1), function() use ($locale, $sortableAndSearchableColumn, $category) {
            return $this->repository->getDataByCategory($locale, $sortableAndSearchableColumn, $category);
        });

        return (ProductResource::collection($result))
                ->additional([
                    'sortableAndSearchableColumn' => $result->sortableAndSearchableColumn
                ]);
    }

    public function showByBrand($locale, $brand)
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

        $searchColumn = [
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

        $sortableAndSearchableColumn = [
            'search'        => $search,
            'search_column' => $searchColumn,
            'sort_column'   => array_merge($search, $searchColumn),
        ];

        $result = Cache::remember('products_by_brand_' . time(), now()->addMinutes(1), function() use ($locale, $sortableAndSearchableColumn, $brand) {
            return $this->repository->getDataByBrand($locale, $sortableAndSearchableColumn, $brand);
        });

        return (ProductResource::collection($result))
                ->additional([
                    'sortableAndSearchableColumn' => $result->sortableAndSearchableColumn,
                ]);
    }

    public function showByUserRecomendation($locale)
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

        $searchColumn = [
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

        $sortableAndSearchableColumn = [
            'search'        => $search,
            'search_column' => $searchColumn,
            'sort_column'   => array_merge($search, $searchColumn),
        ];

        $result = Cache::remember('products_by_recomendation_' . time(), now()->addMinutes(1), function() use ($locale, $sortableAndSearchableColumn) {
            return $this->repository->getDataByUserRecomendation($locale, $sortableAndSearchableColumn);
        });

        return (ProductResource::collection($result))
                ->additional([
                    'sortableAndSearchableColumn' => $result->sortableAndSearchableColumn,
                ]);
    }

    public function search($locale)
    {
        return ProductResource::collection($this->repository->search($locale));
    }

    public function store($locale, $data)
    {
        $request = Arr::only($data, [
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

        $this->repository->validate($request, [
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
        ]);

        DB::beginTransaction();

        $request['code'] = Str::random(15);
        $request['slug'] = Str::slug($request['name']);
        $request['point'] = $request['point'] ?? null;
        $request['weight'] = $request['weight'] ?? null;
        $request['quantity'] = $request['quantity'] ?? null;
        $request['spesification'] = (isset($request['spesification'])) ? json_encode($request['spesification']) : null;
        $result = $this->model->create($request);

        $mainImage = null;

        foreach ($request['images'] as $index => $image) {
            $imageName = uploadImagesToCloudinary($image, 'products');
            $isPrimary = $index === 0 ? 1 : 0;

            $result->product_images()->create(['image' => $imageName, 'is_primary' => $isPrimary]);

            if ($index === 0) {
                $mainImage = $imageName;
            }
        }

        $result->update(['main_image' => $mainImage]);

        DB::commit();

        return new ProductResource($this->repository->getSingleData($locale, $result->id));
    }

    public function update($locale, $id, $data)
    {
        $checkData = $this->repository->getSingleData($locale, $id);

        $data = array_merge([
            'name' => $checkData->name,
            'category_id' => $checkData->category_id,
            'brand_id' => $checkData->brand_id,
            'slug' => $checkData->slug,
            'description' => $checkData->description,
            'point' => $checkData->point,
            'weight' => $checkData->weight,
            'quantity' => $checkData->quantity,
            'spesification' => json_decode($checkData->spesification),
        ], $data);

        $request = Arr::only($data, [
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

        $this->repository->validate($request, [
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
        ]);

        DB::beginTransaction();
        $request['slug'] = Str::slug($request['name']);
        $request['point'] = ($checkData->variants->count() > 0) ? min($checkData->variants->pluck('variant_point')->toArray()) : $request['point'];
        $request['spesification'] = (isset($request['spesification'])) ? json_encode($request['spesification']) : null;
        $checkData->update($request);
        DB::commit();

        return new ProductResource($this->repository->getSingleData($locale, $id));
    }

    public function delete($locale, $id)
    {
        $checkData = $this->repository->getSingleData($locale, $id);

        DB::beginTransaction();

        foreach($checkData->product_images as $image) {
            deleteImagesFromCloudinary($image->image, 'products');
        }

        $result = $checkData->delete();

        DB::commit();

        return new DeletedResource($result);
    }
}
