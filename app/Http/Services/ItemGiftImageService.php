<?php

namespace App\Http\Services;

use Image;
use Illuminate\Support\Arr;
use App\Http\Models\ItemGiftImage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Repositories\ItemGiftImageRepository;

class ItemGiftImageService extends BaseService
{
    private $model, $repository;
    
    public function __construct(ItemGiftImage $model, ItemGiftImageRepository $repository)
    {
        $this->model = $model;
        $this->repository = $repository;
    }

    public function getIndexData($locale, $data)
    {
        $search = [
            'item_gift_id' => 'item_gift_id',
        ];

        $search_column = [
            'id' => 'id',
            'item_gift_id' => 'item_gift_id',
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
            'item_gift_id',
            'item_gift_image',
        ]);

        $this->repository->validate($data_request, [
                'item_gift_id' => [
                    'required',
                    'exists:item_gifts,id',
                ],
                'item_gift_image' => [
                    'required',
                    'max:1000',
                    'mimes:jpg,png'
                ],
            ]
        );

        DB::beginTransaction();
        $image = $data_request['item_gift_image'];
        $image_name = time() . '.' . $image->getClientOriginalExtension();
        Storage::disk('s3')->put('images/' . $image_name, file_get_contents($image));
        $img = Image::make($image);
        $img_thumb = $img->crop(5, 5);
        $img_thumb = $img_thumb->stream()->detach();
        Storage::disk('s3')->put('images/thumbnails/' . $image_name, $img_thumb);
        $result = $this->model->create([
            'item_gift_id' => $data_request['item_gift_id'],
            'item_gift_image' => $image_name,
        ]);
        DB::commit();

        return $this->repository->getSingleData($locale, $result->id);
    }

    public function update($locale, $id, $data)
    {
        $check_data = $this->repository->getSingleData($locale, $id);

        $data = array_merge([
            'item_gift_id' => $check_data->item_gift_id,
            'item_gift_image' => $check_data->item_gift_image,
        ], $data);

        $data_request = Arr::only($data, [
            'item_gift_id',
            'item_gift_image',
        ]);

        $this->repository->validate($data_request, [
                'item_gift_id' => [
                    'required',
                    'exists:item_gifts,id',
                ],
                'item_gift_image' => [
                    'required',
                    'max:1000',
                    'mimes:jpg,png',
                ],
            ]
        );

        DB::beginTransaction();
        if(Storage::disk('s3')->exists('images/' . $check_data->item_gift_image)) {
            Storage::disk('s3')->delete('images/' . $check_data->item_gift_image);
        }
        if(Storage::disk('s3')->exists('images/' . 'thumbnails/' . $check_data->item_gift_image)) {
            Storage::disk('s3')->delete('images/' . 'thumbnails/' . $check_data->item_gift_image);
        }
        $image = $data_request['item_gift_image'];
        $image_name = time() . '.' . $image->getClientOriginalExtension();
        Storage::disk('s3')->put('images/' . $image_name, file_get_contents($image));
        $img = Image::make($image);
        $img_thumb = $img->crop(5, 5);
        $img_thumb = $img_thumb->stream()->detach();
        Storage::disk('s3')->put('images/thumbnails/' . $image_name, $img_thumb);
        $check_data->item_gift_id = $data_request['item_gift_id'];
        $check_data->item_gift_image = $image_name;
        $check_data->save();
        DB::commit();

        return $this->repository->getSingleData($locale, $id);
    }

    public function delete($locale, $id)
    {
        $data = $this->repository->getSingleData($locale, $id);
        
        DB::beginTransaction();
        if(Storage::disk('s3')->exists('images/' . $data->item_gift_image)) {
            Storage::disk('s3')->delete('images/' . $data->item_gift_image);
        }
        if(Storage::disk('s3')->exists('images/' . 'thumbnails/' . $data->item_gift_image)) {
            Storage::disk('s3')->delete('images/' . 'thumbnails/' . $data->item_gift_image);
        }
        $result = $data->delete();
        DB::commit();

        return $result;
    }
}