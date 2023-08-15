<?php

namespace App\Http\Services;

use Image;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Http\Models\ItemGiftImage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Exceptions\ValidationException;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Repositories\ItemGiftRepository;
use App\Http\Repositories\ItemGiftImageRepository;

class ItemGiftImageService extends BaseService
{
    private $model, $repository, $item_gift_image_repository;
    
    public function __construct(ItemGiftImage $model, ItemGiftImageRepository $repository, ItemGiftRepository $item_gift_image_repository)
    {
        $this->model = $model;
        $this->repository = $repository;
        $this->item_gift_image_repository = $repository;
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
        $data_request['item_gift_code'] = Str::uuid();
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
            'item_gift_image' => $check_data->item_gift_image,
        ], $data);

        $data_request = Arr::only($data, [
            'item_gift_image',
        ]);

        $this->repository->validate($data_request, [
                'item_gift_image' => [
                    'required',
                    'max:10000',
                    'mimes:jpg,png',
                ]
            ]
        );

        DB::beginTransaction();
        // $check_data->update($data_request);
        $image = $data_request['item_gift_image'];
        // dd($image);
        $image_name = time() . '.' . $image->getClientOriginalExtension();
        Storage::disk('s3')->put('images/' . $image_name, file_get_contents($image));
        $img = Image::make($image);
        $img_thumb = $img->crop(5, 5);
        $img_thumb = $img_thumb->stream()->detach();
        Storage::disk('s3')->put('images/thumbnails/' . $image_name, $img_thumb);
        $check_data->create([
            'item_gift_id' => $id,
            'item_gift_image' => $image_name,
        ]);
        // $check_data->update($data_request);
        DB::commit();

        return $this->item_gift_image_repository->getSingleData($locale, $id);
    }

    public function delete($locale, $id)
    {
        $check_data = $this->repository->getSingleData($locale, $id);
        
        DB::beginTransaction();
        foreach($check_data->item_gift_image as $image) {
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