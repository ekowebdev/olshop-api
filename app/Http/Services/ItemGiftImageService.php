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

    public function store($locale, $id, $data)
    {
        $check_data = $this->repository->getSingleData($locale, $id);

        $data_request = Arr::only($data, [
            'item_gift_images',
        ]);

        $this->repository->validate($data_request, [
                'item_gift_images' => [
                    'required'
                ],
                'item_gift_images.*' => [
                    'required',
                    'max:10000',
                    'mimes:jpg,png'
                ],
            ]
        );

        DB::beginTransaction();
        if (isset($data_request['item_gift_images'])) {
            foreach ($data_request['item_gift_images'] as $image) {
                $image_name = time() . '.' . $image->getClientOriginalExtension();
                Storage::disk('s3')->put('images/' . $image_name, file_get_contents($image));
                $img = Image::make($image);
                $img_thumb = $img->crop(5, 5);
                $img_thumb = $img_thumb->stream()->detach();
                Storage::disk('s3')->put('images/thumbnails/' . $image_name, $img_thumb);
                $this->model->create([
                    'item_gift_id' => $id,
                    'item_gift_image' => $image_name,
                ]);
            }
        }
        DB::commit();

        return response()->json([
            'message' => 'success upload images',
            'status' => 200,
            'error' => 0
        ]);
    }

    public function delete($locale, $id, $image_name)
    {
        $data = $this->repository->getByIdAndImageName($locale, $id, $image_name);
        
        DB::beginTransaction();
        if(Storage::disk('s3')->exists('images/' . $data->item_gift_image)) {
            Storage::disk('s3')->delete('images/' . $data->item_gift_image);
        }
        if(Storage::disk('s3')->exists('images/' . 'thumbnails/' . $data->item_gift_image)) {
            Storage::disk('s3')->delete('images/' . 'thumbnails/' . $data->item_gift_image);
        }
        $result = $data->where('item_gift_id', $data->item_gift_id)->where('item_gift_image', $data->item_gift_image)->delete();
        DB::commit();

        return $result;
    }
}