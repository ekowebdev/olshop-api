<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Services\WebhookService;
use Illuminate\Support\Facades\Request;
use App\Http\Controllers\BaseController;

class WebhookController extends BaseController
{
    private $service;

    public function __construct(WebhookService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    public function midtrans_handler($locale)
    {
        return $this->service->midtrans_handler($locale, Request::all());
    }
}
