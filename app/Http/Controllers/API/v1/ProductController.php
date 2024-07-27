<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Models\Product;
use App\Http\Services\ProductService;
use App\Http\Resources\DeletedResource;
use App\Http\Resources\ProductResource;
use Illuminate\Support\Facades\Request;
use App\Http\Controllers\BaseController;

class ProductController extends BaseController
{
    private $service;

    public function __construct(ProductService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    public function index($locale)
    {
        $data = $this->service->index($locale, Request::all());
        return (ProductResource::collection($data))
                ->additional([
                    'sortableAndSearchableColumn' => $data->sortableAndSearchableColumn,
                ]);
    }

    public function show($locale, $id)
    {
        $data = $this->service->show($locale, $id);
        return new ProductResource($data);
    }

    public function showBySlug($locale, $slug)
    {
        $data = $this->service->showBySlug($locale, $slug);
        return new ProductResource($data);
    }

    public function showByCategory($locale, $category)
    {
        $data = $this->service->showByCategory($locale, $category);
        return (ProductResource::collection($data))
                ->additional([
                    'sortableAndSearchableColumn' => $data->sortableAndSearchableColumn,
                ]);
    }

    public function showByBrand($locale, $brand)
    {
        $data = $this->service->showByBrand($locale, $brand);
        return (ProductResource::collection($data))
                ->additional([
                    'sortableAndSearchableColumn' => $data->sortableAndSearchableColumn,
                ]);
    }

    public function showByUserRecomendation($locale)
    {
        $data = $this->service->showByUserRecomendation($locale);
        return (ProductResource::collection($data))
                ->additional([
                    'sortableAndSearchableColumn' => $data->sortableAndSearchableColumn,
                ]);
    }

    public function search($locale)
    {
        $data = $this->service->search($locale);
        return ProductResource::collection($data);
    }

    public function store($locale)
    {
        $data = $this->service->store($locale, Request::all());
        return new ProductResource($data);
    }

    public function update($locale, $id)
    {
        $data = $this->service->update($locale, $id, Request::all());
        return new ProductResource($data);
    }

    public function delete($locale, $id)
    {
        $data = $this->service->delete($locale, $id, Request::all());
        return new DeletedResource($data);
    }
}
