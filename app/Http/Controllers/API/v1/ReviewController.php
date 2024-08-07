<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Services\ReviewService;
use App\Http\Resources\ReviewResource;
use App\Http\Resources\DeletedResource;
use Illuminate\Support\Facades\Request;
use App\Http\Controllers\BaseController;

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
        $data = $this->service->index($locale, Request::all());
        return (ReviewResource::collection($data))
                ->additional([
                    'sortableAndSearchableColumn' => $data->sortableAndSearchableColumn,
                ]);
    }

    public function show($locale, $id)
    {
        $data = $this->service->show($locale, $id);
        return new ReviewResource($data);
    }

    public function store($locale)
    {
        return $this->service->store($locale, Request::all());
    }

    public function storeBulk($locale)
    {
        return $this->service->storeBulk($locale, Request::all());
    }

    public function update($locale, $id)
    {
        $data = $this->service->update($locale, $id, Request::all());
        return new ReviewResource($data);
    }

    public function delete($locale, $id)
    {
        $data = $this->service->delete($locale, $id, Request::all());
        return new DeletedResource($data);
    }
}
