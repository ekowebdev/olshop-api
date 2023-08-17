<?php

namespace App\Http\Controllers;

use App\Http\Resources\RedeemResource;
use App\Http\Services\ItemGiftService;
use App\Http\Resources\DeletedResource;
use Illuminate\Support\Facades\Request;
use App\Http\Controllers\BaseController;
use App\Http\Resources\ItemGiftResource;
use Illuminate\Support\Facades\Config;

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

    public function redeem($locale, $id)
    {
        return $this->service->redeem($locale, $id, Request::all());
    }

    public function redeem_multiple($locale)
    {
        return $this->service->redeem_multiple($locale, Request::all());
    }

    public function wishlist($locale, $id)
    {
        return $this->service->wishlist($locale, $id, Request::all());
    }

    public function rating($locale, $id)
    {
        return $this->service->rating($locale, $id, Request::all());
    }
}
