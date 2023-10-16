<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Services\CityService;
use Illuminate\Support\Facades\Request;
use App\Http\Controllers\BaseController;
use App\Http\Resources\CityResource;

class CityController extends BaseController
{
    private $service;

    public function __construct(CityService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    public function index($locale)
    {
        $data = $this->service->getIndexData($locale, Request::all());
        return (CityResource::collection($data))
                ->additional([
                    'sortable_and_searchable_column' => $data->sortableAndSearchableColumn,
                ]);
    }

    public function show($locale, $id)
    {
        $data = $this->service->getSingleData($locale, $id);
        return new CityResource($data);
    }
}
