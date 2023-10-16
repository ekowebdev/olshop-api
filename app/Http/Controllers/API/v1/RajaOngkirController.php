<?php

namespace App\Http\Controllers\API\v1;

use Illuminate\Http\Request;
use App\Http\Resources\CityResource;
use App\Http\Controllers\BaseController;
use App\Http\Resources\ProvinceResource;
use App\Http\Services\RajaOngkirService;
use App\Http\Resources\SubdistrictResource;

class RajaOngkirController extends BaseController
{
    private $service;

    public function __construct(RajaOngkirService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    public function getProvince3rd($locale)
    {
        $id = request('id');
        $page = request('page');
        $per_page = request('per_page');
        return $this->service->getProvince($locale, $id, $page, $per_page);
    }

    public function getCity3rd($locale)
    {
        $id = request('id');
        $province_id = request('province_id');
        $page = request('page');
        $per_page = request('per_page');
        return $this->service->getCity($locale, $id, $province_id, $page, $per_page);
    }

    public function getProvince($locale, Request $request)
    {
        $data = $this->service->getProvince($locale, $request);
        return (ProvinceResource::collection($data))
                ->additional([
                    'sortable_and_searchable_column' => $data->sortableAndSearchableColumn,
                ]);
    }

    public function getCity($locale, Request $request)
    {
        $data = $this->service->getCity($locale, $request);
        return (CityResource::collection($data))
                ->additional([
                    'sortable_and_searchable_column' => $data->sortableAndSearchableColumn,
                ]);
    }

    public function getSubdistrict($locale, Request $request)
    {
        $data = $this->service->getSubdistrict($locale, $request);
        return (SubdistrictResource::collection($data))
                ->additional([
                    'sortable_and_searchable_column' => $data->sortableAndSearchableColumn,
                ]);
    }

    public function getCost($locale, Request $request)
    {
        return $this->service->getCost($locale, $request);
    }
}
