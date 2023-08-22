<?php

namespace App\Http\Controllers\v1;

use App\Http\Services\WishlistService;
use Illuminate\Support\Facades\Request;
use App\Http\Controllers\BaseController;
use App\Http\Resources\WishlistResource;

class WishlistController extends BaseController
{
    private $service;

    public function __construct(WishlistService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    public function index($locale)
    {
        $data = $this->service->getIndexData($locale, Request::all());
        return (WishlistResource::collection($data))
                ->additional([
                    'sortable_and_searchable_column' => $data->sortableAndSearchableColumn,
                ]);
    }

    public function wishlist($locale, $id)
    {
        return $this->service->wishlist($locale, $id, Request::all());
    }
}
