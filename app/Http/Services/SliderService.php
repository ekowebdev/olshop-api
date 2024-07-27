<?php

namespace App\Http\Services;

use App\Http\Models\Slider;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Repositories\SliderRepository;

class SliderService extends BaseService
{
    private $model, $repository;

    public function __construct(Slider $model, SliderRepository $repository)
    {
        $this->model = $model;
        $this->repository = $repository;
    }

    public function index($locale, $data)
    {
        $search = [
            'title' => 'title',
            'link' => 'link',
            'sort' => 'sort',
            'start_date' => 'start_date',
            'end_date' => 'end_date',
        ];

        $searchColumn = [
            'id' => 'id',
            'title' => 'title',
            'link' => 'link',
            'sort' => 'sort',
            'start_date' => 'start_date',
            'end_date' => 'end_date',
        ];

        $sortableAndSearchableColumn = [
            'search'        => $search,
            'search_column' => $searchColumn,
            'sort_column'   => array_merge($search, $searchColumn),
        ];

        return $this->repository->getAllData($locale, $sortableAndSearchableColumn);
    }

    public function show($locale, $id)
    {
        return $this->repository->getSingleData($locale, $id);
    }

    public function showByActive($locale, $data)
    {
        $search = [
            'title' => 'title',
            'link' => 'link',
            'sort' => 'sort',
            'start_date' => 'start_date',
            'end_date' => 'end_date',
        ];

        $searchColumn = [
            'id' => 'id',
            'title' => 'title',
            'link' => 'link',
            'sort' => 'sort',
            'start_date' => 'start_date',
            'end_date' => 'end_date',
        ];

        $sortableAndSearchableColumn = [
            'search'        => $search,
            'search_column' => $searchColumn,
            'sort_column'   => array_merge($search, $searchColumn),
        ];

        return $this->repository->getListDataByActive($locale, $sortableAndSearchableColumn);
    }

    public function store($locale, $data)
    {
        $request = Arr::only($data, [
            'title',
            'description',
            'link',
            'sort',
            'image',
            'start_date',
            'end_date',
        ]);

        $this->repository->validate($request, [
            'title' => [
                'nullable',
                'string',
            ],
            'description' => [
                'nullable',
                'string',
            ],
            'link' => [
                'nullable',
                'string',
            ],
            'sort' => [
                'required',
                'integer',
                'unique:sliders,sort',
            ],
            'start_date' => [
                'required',
                'date'
            ],
            'end_date' => [
                'required',
                'date',
                'after:start_date'
            ],
            'image' => [
                'required',
                'max:1000',
                'image',
                'mimes:jpg,png',
                'dimensions:width=100,height=100'
            ],
        ]);

        DB::beginTransaction();

        $file = Request::file('image');

        $imageName = uploadImagesToCloudinary($file, 'sliders');

        $result = $this->model->create([
            'title' => $request['title'],
            'description' => $request['description'],
            'link' => config('setting.frontend.url') . '/' . $request['link'],
            'sort' => $request['sort'],
            'start_date' => $request['start_date'],
            'end_date' => $request['end_date'],
            'image' => $imageName,
        ]);

        DB::commit();

        return $this->repository->getSingleData($locale, $result->id);
    }

    public function update($locale, $id, $data)
    {
        $checkData = $this->repository->getSingleData($locale, $id);

        $request = Arr::only($data, [
            'title',
            'description',
            'link',
            'sort',
            'image',
            'start_date',
            'end_date',
        ]);

        $this->repository->validate($request, [
            'sort' => [
                'integer',
                'unique:sliders,sort,' . $id,
            ],
            'start_date' => [
                'date'
            ],
            'end_date' => [
                'date',
                'after:start_date'
            ],
            'image' => [
                'max:1000',
                'image',
                'mimes:jpg,png',
            ],
        ]);

        DB::beginTransaction();

        if (isset($request['image'])) {
            $file = Request::file('image');

            if ($checkData->image) {
                deleteImagesFromCloudinary($checkData->image, 'sliders');
            }

            $imageName = uploadImagesToCloudinary($file, 'sliders');

            $checkData->image = $imageName;
        }

        $checkData->title = $request['title'] ?? $checkData->title;
        $checkData->description = $request['description'] ?? $checkData->description;
        $checkData->link = isset($request['link']) ? config('setting.frontend.url') . '/' . $request['link'] : $checkData->link;
        $checkData->sort = $request['sort'] ?? $checkData->sort;
        $checkData->start_date = $request['start_date'] ?? $checkData->start_date;
        $checkData->end_date = $request['end_date'] ?? $checkData->end_date;
        $checkData->save();

        DB::commit();

        return $this->repository->getSingleData($locale, $id);
    }

    public function delete($locale, $id)
    {
        $checkData = $this->repository->getSingleData($locale, $id);

        DB::beginTransaction();
        deleteImagesFromCloudinary($checkData->image, 'sliders');
        $result = $checkData->delete();
        DB::commit();

        return $result;
    }
}
