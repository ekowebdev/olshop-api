<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Services\VariantService;
use App\Http\Resources\DeletedResource;
use Illuminate\Support\Facades\Request;
use App\Http\Controllers\BaseController;
use App\Http\Resources\VariantResource;

class VariantController extends BaseController
{
    private $service;

    public function __construct(VariantService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    public function index($locale)
    {
        $data = $this->service->getIndexData($locale, Request::all());
        return (VariantResource::collection($data))
                ->additional([
                    'sortable_and_searchable_column' => $data->sortableAndSearchableColumn,
                ]);
    }

    public function show($locale, $id)
    {
        $data = $this->service->getSingleData($locale, $id);
        return new VariantResource($data);
    }

    public function store($locale)
    {
        $data = $this->service->store($locale, Request::all());
        return new VariantResource($data);
    }

    public function update($locale, $id)
    {
        $data = $this->service->update($locale, $id, Request::all());
        return new VariantResource($data);
    }

    public function delete($locale, $id)
    {
        $data = $this->service->delete($locale, $id, Request::all());
        return new DeletedResource($data);
    }
}
