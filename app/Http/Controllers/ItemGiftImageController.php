<?php

namespace App\Http\Controllers;

use App\Http\Resources\DeletedResource;
use Illuminate\Support\Facades\Request;
use App\Http\Controllers\BaseController;
use App\Http\Resources\ItemGiftResource;
use App\Http\Services\ItemGiftImageService;
use App\Http\Resources\ItemGiftImageResource;

class ItemGiftImageController extends BaseController
{
    private $service;

    public function __construct(ItemGiftImageService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    public function store($locale, $id)
    {
        return $this->service->store($locale, $id, Request::all());
    }
    
    public function delete($locale, $id, $image_name)
    {
        $data = $this->service->delete($locale, $id, $image_name, Request::all());
        return new DeletedResource($data);
    }
}
