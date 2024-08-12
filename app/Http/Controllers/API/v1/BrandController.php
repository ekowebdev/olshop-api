<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Services\BrandService;
use App\Http\Resources\DeletedResource;
use Illuminate\Support\Facades\Request;
use App\Http\Controllers\BaseController;
use App\Http\Resources\BrandResource;

class BrandController extends BaseController
{
    private $service;

    public function __construct(BrandService $service)
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
