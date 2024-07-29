<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Services\TrackReceiptService;
use Illuminate\Support\Facades\Request;
use App\Http\Controllers\BaseController;

class TrackReceiptController extends BaseController
{
    private $service;

    public function __construct(TrackReceiptService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    public function __invoke($locale)
    {
        return $this->service->track($locale, Request::all());
    }
}
