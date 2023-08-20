<?php

namespace App\Http\Controllers;

use App\Http\Services\RatingService;
use App\Http\Resources\DeletedResource;
use Illuminate\Support\Facades\Request;
use App\Http\Controllers\BaseController;
use App\Http\Resources\RatingResource;

class RatingController extends BaseController
{
    private $service;

    public function __construct(RatingService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    public function index($locale)
    {
        $data = $this->service->getIndexData($locale, Request::all());
        return (RatingResource::collection($data))
                ->additional([
                    'sortable_and_searchable_column' => $data->sortableAndSearchableColumn,
                ]);
    }

    public function show($locale, $id)
    {
        $data = $this->service->getSingleData($locale, $id);
        return new RatingResource($data);
    }

    public function rating($locale, $id)
    {
        return $this->service->rating($locale, $id, Request::all());
    }
}
