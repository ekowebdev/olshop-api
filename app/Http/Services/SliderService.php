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

    public function getIndexData($locale, $data)
    {
        $search = [
            'title' => 'title',
            'link' => 'link',
            'sort' => 'sort',
            'start_date' => 'start_date',
            'end_date' => 'end_date',
        ];

        $search_column = [
            'id' => 'id',
            'title' => 'title',
            'link' => 'link',
            'sort' => 'sort',
            'start_date' => 'start_date',
            'end_date' => 'end_date',
        ];

        $sortable_and_searchable_column = [
            'search'        => $search,
            'search_column' => $search_column,
            'sort_column'   => array_merge($search, $search_column),
        ];

        return $this->repository->getIndexData($locale, $sortable_and_searchable_column);
    }

    public function getSingleData($locale, $id)
    {
        return $this->repository->getSingleData($locale, $id);
    }

    public function getListDataByActive($locale, $data)
    {
        $search = [
            'title' => 'title',
            'link' => 'link',
            'sort' => 'sort',
            'start_date' => 'start_date',
            'end_date' => 'end_date',
        ];

        $search_column = [
            'id' => 'id',
            'title' => 'title',
            'link' => 'link',
            'sort' => 'sort',
            'start_date' => 'start_date',
            'end_date' => 'end_date',
        ];

        $sortable_and_searchable_column = [
            'search'        => $search,
            'search_column' => $search_column,
            'sort_column'   => array_merge($search, $search_column),
        ];

        return $this->repository->getListDataByActive($locale, $sortable_and_searchable_column);
    }

    public function store($locale, $data)
    {
        $data_request = Arr::only($data, [
            'title',
            'description',
            'link',
            'sort',
            'image',
            'start_date',
            'end_date',
        ]);

        $this->repository->validate($data_request, [
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
            ]
        );

        DB::beginTransaction();
        $image = $data_request['image'];
        $image_name = time() . '.' . $image->getClientOriginalExtension();
        Storage::disk('google')->put('images/slider/' . $image_name, file_get_contents($image));
        $img = Image::make($image);
        $img_thumb = $img->crop(5, 5);
        $img_thumb = $img_thumb->stream()->detach();
        Storage::disk('google')->put('images/slider/thumbnails/' . $image_name, $img_thumb);
        $result = $this->model->create([
            'title' => $data_request['title'],
            'description' => $data_request['description'],
            'link' => config('setting.frontend.url') . '/' . $data_request['link'],
            'sort' => $data_request['sort'],
            'start_date' => $data_request['start_date'],
            'end_date' => $data_request['end_date'],
            'image' => $image_name,
        ]);
        DB::commit();

        return $this->repository->getSingleData($locale, $result->id);
    }

    public function update($locale, $id, $data)
    {
        $check_data = $this->repository->getSingleData($locale, $id);

        $data_request = Arr::only($data, [
            'title',
            'description',
            'link',
            'sort',
            'image',
            'start_date',
            'end_date',
        ]);

        $this->repository->validate($data_request, [
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
        if (isset($data_request['image'])) {
            if(Storage::disk('google')->exists('images/slider/' . $check_data->image)) {
                Storage::disk('google')->delete('images/slider/' . $check_data->image);
            }
            if(Storage::disk('google')->exists('images/slider/thumbnails/' . $check_data->image)) {
                Storage::disk('google')->delete('images/slider/thumbnails/' . $check_data->image);
            }
            $image = $data_request['image'];
            $image_name = time() . '.' . $image->getClientOriginalExtension();
            Storage::disk('google')->put('images/slider/' . $image_name, file_get_contents($image));
            $img = Image::make($image);
            $img_thumb = $img->crop(5, 5);
            $img_thumb = $img_thumb->stream()->detach();
            Storage::disk('google')->put('images/slider/thumbnails/' . $image_name, $img_thumb);
            $check_data->image = $image_name;
        }
        $check_data->title = $data_request['title'] ?? $check_data->title;
        $check_data->description = $data_request['description'] ?? $check_data->description;
        $check_data->link = isset($data_request['link']) ? config('setting.frontend.url') . '/' . $data_request['link'] : $check_data->link;
        $check_data->sort = $data_request['sort'] ?? $check_data->sort;
        $check_data->start_date = $data_request['start_date'] ?? $check_data->start_date;
        $check_data->end_date = $data_request['end_date'] ?? $check_data->end_date;
        $check_data->save();
        DB::commit();

        return $this->repository->getSingleData($locale, $id);
    }

    public function delete($locale, $id)
    {
        $check_data = $this->repository->getSingleData($locale, $id);
        DB::beginTransaction();
        if(Storage::disk('google')->exists('images/slider/' . $check_data->image)) {
            Storage::disk('google')->delete('images/slider/' . $check_data->image);
        }
        $result = $check_data->delete();
        DB::commit();

        return $result;
    }
}
