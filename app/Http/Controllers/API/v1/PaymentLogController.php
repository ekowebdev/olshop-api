<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Services\PaymentLogService;
use App\Http\Resources\PaymentLogResource;
use Illuminate\Support\Facades\Request;
use App\Http\Controllers\BaseController;

class PaymentLogController extends BaseController
{
    private $service;

    public function __construct(PaymentLogService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    public function index($locale)
    {
        $data = $this->service->getIndexData($locale, Request::all());
        return (PaymentLogResource::collection($data))
                ->additional([
                    'sortable_and_searchable_column' => $data->sortableAndSearchableColumn,
                ]);
    }

    public function show($locale, $id)
    {
        $data = $this->service->getSingleData($locale, $id);
        return new PaymentLogResource($data);
    }
}
