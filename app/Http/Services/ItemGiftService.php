<?php

namespace App\Http\Services;

use Image;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Http\Models\ItemGift;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Exceptions\ValidationException;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Repositories\ItemGiftRepository;

class ItemGiftService extends BaseService
{
    private $model, $repository;
    
    public function __construct(ItemGift $model, ItemGiftRepository $repository)
    {
        $this->model = $model;
        $this->repository = $repository;
    }

    public function getIndexData($locale, $data)
    {
        $search = [
            'item_gift_name' => 'item_gift_name',
            'item_gift_quantity' => 'item_gift_quantity',
            'item_gift_point' => 'item_gift_point',
            'total_rating' => 'total_rating',
        ];

        $search_column = [
            'id' => 'id',
            'item_gift_name' => 'item_gift_name',
            'item_gift_quantity' => 'item_gift_quantity',
            'item_gift_point' => 'item_gift_point',
            'total_rating' => 'total_rating',
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

    public function store($locale, $data)
    {
        $data_request = Arr::only($data, [
            'item_gift_name',
            'item_gift_description',
            'item_gift_point',
            'item_gift_quantity',
            'item_gift_images',
        ]);

        $this->repository->validate($data_request, [
                'item_gift_name' => [
                    'required'
                ],
                'item_gift_description' => [
                    'required'
                ],
                'item_gift_point' => [
                    'required',
                    'numeric'
                ],
                'item_gift_quantity' => [
                    'required',
                    'numeric'
                ],
                'item_gift_images.*' => [
                    'max:10000',
                    'mimes:jpg,png'
                ],
            ]
        );

        DB::beginTransaction();
        $data_request['item_gift_code'] = Str::random(15);
        $result = $this->model->create($data_request);
        if (isset($data_request['item_gift_images'])) {
            foreach ($data_request['item_gift_images'] as $image) {
                $image_name = time() . '.' . $image->getClientOriginalExtension();
                Storage::disk('s3')->put('images/' . $image_name, file_get_contents($image));
                $img = Image::make($image);
                $img_thumb = $img->crop(5, 5);
                $img_thumb = $img_thumb->stream()->detach();
                Storage::disk('s3')->put('images/thumbnails/' . $image_name, $img_thumb);
                $result->item_gift_images()->create([
                    'item_gift_image' => $image_name,
                ]);
            }
        }
        DB::commit();

        return $this->repository->getSingleData($locale, $result->id);
    }

    public function update($locale, $id, $data)
    {
        $check_data = $this->repository->getSingleData($locale, $id);

        $data = array_merge([
            'item_gift_code' => $check_data->item_gift_code,
            'item_gift_name' => $check_data->item_gift_name,
            'item_gift_description' => $check_data->item_gift_description,
            'item_gift_point' => $check_data->item_gift_point,
            'item_gift_quantity' => $check_data->item_gift_quantity,
        ], $data);

        $data_request = Arr::only($data, [
            'item_gift_name',
            'item_gift_description',
            'item_gift_point',
            'item_gift_quantity',
        ]);

        $this->repository->validate($data_request, [
                'item_gift_name' => [
                    'required'
                ],
                'item_gift_description' => [
                    'required'
                ],
                'item_gift_point' => [
                    'required',
                    'numeric'
                ],
                'item_gift_quantity' => [
                    'required',
                    'numeric'
                ]
            ]
        );

        DB::beginTransaction();
        $check_data->update($data_request);
        DB::commit();

        return $this->repository->getSingleData($locale, $id);
    }

    public function delete($locale, $id)
    {
        $check_data = $this->repository->getSingleData($locale, $id);
        
        DB::beginTransaction();
        foreach($check_data->item_gift_images as $image) {
            if(Storage::disk('s3')->exists('images/' . $image->item_gift_image)) {
                Storage::disk('s3')->delete('images/' . $image->item_gift_image);
            }
            if(Storage::disk('s3')->exists('images/' . 'thumbnails/' . $image->item_gift_image)) {
                Storage::disk('s3')->delete('images/' . 'thumbnails/' . $image->item_gift_image);
            }
        }
        $result = $check_data->delete();
        DB::commit();

        return $result;
    }
}
