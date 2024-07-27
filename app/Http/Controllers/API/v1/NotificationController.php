<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Models\Notification;
use App\Http\Resources\DeletedResource;
use Illuminate\Support\Facades\Request;
use App\Http\Controllers\BaseController;
use App\Http\Services\NotificationService;
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
        return $this->service->index($locale, Request::all());
    }

    public function show($locale, $id)
    {
        $data = $this->service->show($locale, $id);
        return new NotificationResource($data);
    }

    public function showByUser($locale, $id)
    {
        return $this->service->showByUser($locale, $id);
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
