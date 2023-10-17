<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Services\ItemGiftService;
use App\Http\Resources\DeletedResource;
use Illuminate\Support\Facades\Request;
use App\Http\Controllers\BaseController;
use App\Http\Resources\ItemGiftResource;

class ItemGiftController extends BaseController
{
    private $service;

    public function __construct(ItemGiftService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    public function index($locale)
    {
        $data = $this->service->getIndexData($locale, Request::all());
        return (ItemGiftResource::collection($data))
                ->additional([
                    'sortable_and_searchable_column' => $data->sortableAndSearchableColumn,
                ]);
    }

    public function show($locale, $id)
    {
        $data = $this->service->getSingleData($locale, $id);
        return new ItemGiftResource($data);
    }

    public function showBySlug($locale, $slug)
    {
        $data = $this->service->getSingleDataBySlug($locale, $slug);
        return new ItemGiftResource($data);
    }

    public function showByCategory($locale, $category)
    {
        $data = $this->service->getDataByCategory($locale, $category);
        return (ItemGiftResource::collection($data))
                ->additional([
                    'sortable_and_searchable_column' => $data->sortableAndSearchableColumn,
                ]);
    }

    public function showByBrand($locale, $brand)
    {
        $data = $this->service->getDataByBrand($locale, $brand);
        return (ItemGiftResource::collection($data))
                ->additional([
                    'sortable_and_searchable_column' => $data->sortableAndSearchableColumn,
                ]);
    }

    public function store($locale)
    {
        $data = $this->service->store($locale, Request::all());
        return new ItemGiftResource($data);
    }

    public function update($locale, $id)
    {
        $data = $this->service->update($locale, $id, Request::all());
        return new ItemGiftResource($data);
    }

    public function delete($locale, $id)
    {
        $data = $this->service->delete($locale, $id, Request::all());
        return new DeletedResource($data);
    }
}
