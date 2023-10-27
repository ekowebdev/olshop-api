<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Services\WishlistService;
use Illuminate\Support\Facades\Request;
use App\Http\Controllers\BaseController;
use App\Http\Resources\WishlistResource;

class WishlistController extends BaseController
{
    private $service;

    public function __construct(WishlistService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    public function index($locale)
    {
        $data = $this->service->getIndexData($locale, Request::all());
        return (WishlistResource::collection($data));
    }

    public function show($locale, $id)
    {
        $data = $this->service->getSingleData($locale, $id);
        return new WishlistResource($data);
    }

    public function showByUser($locale, $id)
    {
        $data = $this->service->getDataByUser($locale, $id);
        return (WishlistResource::collection($data));
    }

    public function wishlist($locale, $id)
    {
        return $this->service->wishlist($locale, $id, Request::all());
    }
}
