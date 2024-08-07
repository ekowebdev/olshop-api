<?php

namespace App\Http\Controllers\API\v1;

use Illuminate\Support\Facades\Request;
use App\Http\Controllers\BaseController;
use App\Http\Services\RajaOngkirService;

class RajaOngkirController extends BaseController
{
    private $service;

    public function __construct(RajaOngkirService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    public function getProvince($locale)
    {
        $id = request('id');
        $page = request('page');
        $perPage = request('per_page');
        return $this->service->getProvince($locale, $id, $page, $perPage);
    }

    public function getCity($locale)
    {
        $id = request('id');
        $provinceId = request('province_id');
        $page = request('page');
        $perPage = request('per_page');
        return $this->service->getCity($locale, $id, $provinceId, $page, $perPage);
    }

    public function getCost($locale)
    {
        return $this->service->getCost($locale, Request::all());
    }
}
