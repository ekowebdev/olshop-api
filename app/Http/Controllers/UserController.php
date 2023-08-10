<?php

namespace App\Http\Controllers;

use App\Http\Resources\RedeemResource;
use App\Http\Services\UserService;
use App\Http\Resources\DeletedResource;
use Illuminate\Support\Facades\Request;
use App\Http\Controllers\BaseController;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Config;

class UserController extends BaseController
{
    private $service;

    public function __construct(UserService $service)
    {
        $this->service = $service;
        parent::__construct();
    }

    public function index($locale)
    {
        $data = $this->service->getIndexData($locale, Request::all());
        return (UserResource::collection($data))
                ->additional([
                    'sortable_and_searchable_column' => $data->sortableAndSearchableColumn,
                ]);
    }

    public function show($locale, $id)
    {
        $data = $this->service->getSingleData($locale, $id);
        return new UserResource($data);
    }

    public function store($locale)
    {
        $data = $this->service->store($locale, Request::all());
        return new UserResource($data);
    }

    public function update($locale, $id)
    {
        $data = $this->service->update($locale, $id, Request::all());
        return new UserResource($data);
    }

    public function delete($locale, $id)
    {
        $data = $this->service->delete($locale, $id, Request::all());
        return new DeletedResource($data);
    }
}
