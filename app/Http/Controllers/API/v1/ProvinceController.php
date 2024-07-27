<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Services\ProvinceService;
use Illuminate\Support\Facades\Request;
use App\Http\Controllers\BaseController;
use App\Http\Resources\ProvinceResource;

class ProvinceController extends BaseController
{
    private $service;

    public function __construct(ProvinceService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    public function index($locale)
    {
        $data = $this->service->index($locale, Request::all());
        return (ProvinceResource::collection($data))
                ->additional([
                    'sortableAndSearchableColumn' => $data->sortableAndSearchableColumn,
                ]);
    }

    public function show($locale, $id)
    {
        $data = $this->service->show($locale, $id);
        return new ProvinceResource($data);
    }
}
