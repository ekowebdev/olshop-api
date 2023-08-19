<?php

namespace App\Http\Controllers;

use App\Http\Resources\RedeemResource;
use App\Http\Services\RedeemService;
use Illuminate\Support\Facades\Request;
use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\Config;

class RedeemController extends BaseController
{
    private $service;

    public function __construct(RedeemService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    public function index($locale)
    {
        $data = $this->service->getIndexData($locale, Request::all());
        return (RedeemResource::collection($data))
                ->additional([
                    'sortable_and_searchable_column' => $data->sortableAndSearchableColumn,
                ]);
    }

    public function show($locale, $id)
    {
        $data = $this->service->getSingleData($locale, $id);
        return new RedeemResource($data);
    }

    public function redeem($locale, $id)
    {
        return $this->service->redeem($locale, $id, Request::all());
    }

    public function redeem_multiple($locale)
    {
        return $this->service->redeem_multiple($locale, Request::all());
    }
}
