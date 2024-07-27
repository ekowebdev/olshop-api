<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Services\CartService;
use App\Http\Resources\DeletedResource;
use Illuminate\Support\Facades\Request;
use App\Http\Controllers\BaseController;
use App\Http\Resources\CartResource;

class CartController extends BaseController
{
    private $service;

    public function __construct(CartService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    public function index($locale)
    {
        $data = $this->service->index($locale, Request::all());
        return (CartResource::collection($data));
    }

    public function show($locale, $id)
    {
        $data = $this->service->show($locale, $id);
        return new CartResource($data);
    }

    public function showByUser($locale, $id)
    {
        $data = $this->service->showByUser($locale, $id);
        return (CartResource::collection($data));
    }

    public function store($locale)
    {
        return $this->service->store($locale, Request::all());
    }

    public function update($locale, $id)
    {
        $data = $this->service->update($locale, $id, Request::all());
        return new CartResource($data);
    }

    public function delete($locale, $id)
    {
        $data = $this->service->delete($locale, $id, Request::all());
        return new DeletedResource($data);
    }
}
