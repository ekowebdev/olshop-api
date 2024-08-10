<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Models\Product;
use App\Http\Services\ProductService;
use App\Http\Resources\DeletedResource;
use App\Http\Resources\ProductResource;
use Illuminate\Support\Facades\Request;
use App\Http\Controllers\BaseController;
use App\Http\Repositories\WishlistRepository;

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
        return $this->service->index($locale, Request::all());
    }

    public function show($locale, $id)
    {
        return $this->service->show($locale, $id);
    }

    public function showBySlug($locale, $slug)
    {
        return $this->service->showBySlug($locale, $slug);
    }

    public function showByCategory($locale, $category)
    {
        return $this->service->showByCategory($locale, $category);
    }

    public function showByBrand($locale, $brand)
    {
        return $this->service->showByBrand($locale, $brand);
    }

    public function showByUserRecomendation($locale)
    {
        return $this->service->showByUserRecomendation($locale);
    }

    public function search($locale)
    {
        return $this->service->search($locale);
    }

    public function store($locale)
    {
        return $this->service->store($locale, Request::all());
    }

    public function update($locale, $id)
    {
        return $this->service->update($locale, $id, Request::all());
    }

    public function delete($locale, $id)
    {
        return $this->service->delete($locale, $id, Request::all());
    }
}
