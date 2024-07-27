<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Services\OrderService;
use App\Http\Resources\OrderResource;
use App\Http\Resources\DeletedResource;
use Illuminate\Support\Facades\Request;
use App\Http\Controllers\BaseController;

class OrderController extends BaseController
{
    private $service;

    public function __construct(OrderService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    public function index($locale)
    {
        $data = $this->service->index($locale, Request::all());
        return (OrderResource::collection($data))
                ->additional([
                    'sortableAndSearchableColumn' => $data->sortableAndSearchableColumn,
                ]);
    }

    public function show($locale, $id)
    {
        $data = $this->service->show($locale, $id);
        return new OrderResource($data);
    }

    public function showByUser($locale, $id)
    {
        $data = $this->service->showByUser($locale, Request::all(), $id);
        return (OrderResource::collection($data))
                ->additional([
                    'sortableAndSearchableColumn' => $data->sortableAndSearchableColumn,
                ]);
    }

    public function checkout($locale)
    {
        return $this->service->checkout($locale, Request::all());
    }

    public function cancel($locale, $id)
    {
        return $this->service->cancel($locale, $id, Request::all());
    }

    public function receive($locale, $id)
    {
        return $this->service->receive($locale, $id, Request::all());
    }

    public function delete($locale, $id)
    {
        $data = $this->service->delete($locale, $id, Request::all());
        return new DeletedResource($data);
    }
}
