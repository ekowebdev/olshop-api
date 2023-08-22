<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Services\ReviewService;
use Illuminate\Support\Facades\Request;
use App\Http\Controllers\BaseController;
use App\Http\Resources\ReviewResource;

class ReviewController extends BaseController
{
    private $service;

    public function __construct(ReviewService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    public function index($locale)
    {
        $data = $this->service->getIndexData($locale, Request::all());
        return (ReviewResource::collection($data))
                ->additional([
                    'sortable_and_searchable_column' => $data->sortableAndSearchableColumn,
                ]);
    }

    public function show($locale, $id)
    {
        $data = $this->service->getSingleData($locale, $id);
        return new ReviewResource($data);
    }

    public function rating($locale, $id)
    {
        return $this->service->rating($locale, $id, Request::all());
    }
}
