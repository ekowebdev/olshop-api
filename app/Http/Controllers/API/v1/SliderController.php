<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Services\SliderService;
use App\Http\Resources\DeletedResource;
use Illuminate\Support\Facades\Request;
use App\Http\Controllers\BaseController;
use App\Http\Resources\SliderResource;

class SliderController extends BaseController
{
    private $service;

    public function __construct(SliderService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    public function index($locale)
    {
        $data = $this->service->index($locale, Request::all());
        return (SliderResource::collection($data))
                ->additional([
                    'sortableAndSearchableColumn' => $data->sortableAndSearchableColumn,
                ]);
    }

    public function show($locale, $id)
    {
        $data = $this->service->show($locale, $id);
        return new SliderResource($data);
    }

    public function showByActive($locale)
    {
        $data = $this->service->showByActive($locale, Request::all());
        return (SliderResource::collection($data))
                ->additional([
                    'sortableAndSearchableColumn' => $data->sortableAndSearchableColumn,
                ]);
    }

    public function store($locale)
    {
        $data = $this->service->store($locale, Request::all());
        return new SliderResource($data);
    }

    public function update($locale, $id)
    {
        $data = $this->service->update($locale, $id, Request::all());
        return new SliderResource($data);
    }

    public function delete($locale, $id)
    {
        $data = $this->service->delete($locale, $id, Request::all());
        return new DeletedResource($data);
    }
}
