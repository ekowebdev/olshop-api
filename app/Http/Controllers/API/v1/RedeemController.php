<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Services\RedeemService;
use App\Http\Resources\RedeemResource;
use App\Http\Resources\DeletedResource;
use Illuminate\Support\Facades\Request;
use App\Http\Controllers\BaseController;

class RedeemController extends BaseController
{
    private $service;

    public function __construct(RedeemService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    public function index($locale)
    {
        $data = $this->service->getIndexData($locale, Request::all());
        return (RedeemResource::collection($data))
                ->additional([
                    'sortable_and_searchable_column' => $data->sortableAndSearchableColumn,
                ]);
    }

    public function show($locale, $id)
    {
        $data = $this->service->getSingleData($locale, $id);
        return new RedeemResource($data);
    }

    public function checkout($locale)
    {
        return $this->service->checkout($locale, Request::all());
    }

    public function delete($locale, $id)
    {
        $data = $this->service->delete($locale, $id, Request::all());
        return new DeletedResource($data);
    }
}
