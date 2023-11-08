<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Services\TrackResiService;
use Illuminate\Support\Facades\Request;
use App\Http\Controllers\BaseController;

class TrackResiController extends BaseController
{
    private $service;

    public function __construct(TrackResiService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    public function track($locale)
    {
        return $this->service->track($locale, Request::all());
    }
}
