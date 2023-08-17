<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Config;
use App\Http\Resources\DeletedResource;
use Illuminate\Support\Facades\Request;
use App\Http\Controllers\BaseController;
use App\Http\Services\ItemGiftImageService;
use App\Http\Resources\ItemGiftResource;

class ItemGiftImageController extends BaseController
{
    private $service;

    public function __construct(ItemGiftImageService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    public function update($locale, $id)
    {
        $data = $this->service->update($locale, $id, Request::all());
        return new ItemGiftResource($data);
    }

    public function delete($locale, $id)
    {
        $data = $this->service->delete($locale, $id, Request::all());
        return new DeletedResource($data);
    }
}
