<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Services\NotificationService;
use App\Http\Resources\DeletedResource;
use Illuminate\Support\Facades\Request;
use App\Http\Controllers\BaseController;
use App\Http\Resources\NotificationResource;

class NotificationController extends BaseController
{
    private $service;

    public function __construct(NotificationService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    public function index($locale)
    {
        $data = $this->service->getIndexData($locale, Request::all());
        return (NotificationResource::collection($data));
    }

    public function show($locale, $id)
    {
        $data = $this->service->getSingleData($locale, $id);
        return new NotificationResource($data);
    }

    public function showByUser($locale, $id)
    {
        $data = $this->service->getDataByUser($locale, $id);
        return (NotificationResource::collection($data));
    }

    public function store($locale)
    {
        $data = $this->service->store($locale, Request::all());
        return new NotificationResource($data);
    }

    public function update($locale, $id)
    {
        $data = $this->service->update($locale, $id, Request::all());
        return new NotificationResource($data);
    }

    public function delete($locale, $id)
    {
        $data = $this->service->delete($locale, $id, Request::all());
        return new DeletedResource($data);
    }
}
