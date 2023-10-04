<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Services\RajaOngkirService;
use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;

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
        $per_page = request('per_page');
        return $this->service->getProvince($locale, $id, $page, $per_page);
    }

    public function getCity($locale)
    {
        $id = request('id');
        $province_id = request('province_id');
        $page = request('page');
        $per_page = request('per_page');
        return $this->service->getCity($locale, $id, $province_id, $page, $per_page);
    }

    public function getCost($locale, Request $request)
    {
        return $this->service->getCost($locale, $request);
    }
}
