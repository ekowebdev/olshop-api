<?php

namespace App\Http\Services;

use Image;
use Illuminate\Support\Arr;
use App\Http\Models\Variant;
use App\Http\Models\ProductImage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Exceptions\ApplicationException;
use App\Http\Repositories\ProductImageRepository;

class ProductImageService extends BaseService
{
    private $model, $repository;

    public function __construct(ProductImage $model, ProductImageRepository $repository)
    {
        $this->model = $model;
        $this->repository = $repository;
    }

    public function getIndexData($locale, $data)
    {
        $search = [
            'product_id' => 'product_id',
            'variant_id' => 'variant_id',
        ];

        $search_column = [
            'id' => 'id',
            'product_id' => 'product_id',
            'variant_id' => 'variant_id',
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
            'product_id',
            'variant_id',
            'image',
        ]);

        $this->repository->validate($data_request, [
                'product_id' => [
                    'required',
                    'exists:products,id',
                ],
                'variant_id' => [
                    'nullable',
                    'exists:variants,id',
                ],
                'image' => [
                    'required',
                    'max:1000',
                    'image',
                    'mimes:jpg,png',
                ],
            ]
        );

        DB::beginTransaction();

        if(isset($data_request['variant_id'])){
            $variant = Variant::where('id', $data_request['variant_id'])->where('product_id', $data_request['product_id'])->first();
            if(is_null($variant)) throw new ApplicationException(trans('error.variant_not_found_in_products', ['product_name' => $variant->products->name]));

            $exists_variant = $this->repository->getSingleDataByProductVariant($locale, $data_request['product_id'], $data_request['variant_id']);
            if(!is_null($exists_variant)) throw new ApplicationException(trans('error.exists_image_variant_products', ['product_name' => $variant->products->name, 'variant_name' => $variant->name]));
        }

        $image = $data_request['image'];
        $image_name = time() . '.' . $image->getClientOriginalExtension();
        Storage::disk('google')->put('images/' . $image_name, file_get_contents($image));
        $img = Image::make($image);
        $img_thumb = $img->crop(5, 5);
        $img_thumb = $img_thumb->stream()->detach();
        Storage::disk('google')->put('images/thumbnails/' . $image_name, $img_thumb);

        $result = $this->model->create([
            'product_id' => $data_request['product_id'],
            'variant_id' => (isset($data_request['variant_id'])) ? $data_request['variant_id'] : null,
            'image' => $image_name,
        ]);

        DB::commit();

        return $this->repository->getSingleData($locale, $result->id);
    }

    public function update($locale, $id, $data)
    {
        $check_data = $this->repository->getSingleData($locale, $id);

        $data_request = Arr::only($data, [
            'product_id',
            'variant_id',
            'image',
        ]);

        $this->repository->validate($data_request, [
                'product_id' => [
                    'exists:products,id',
                ],
                'variant_id' => [
                    'exists:variants,id',
                ],
                'image' => [
                    'max:1000',
                    'image',
                    'mimes:jpg,png',
                ],
            ]
        );

        DB::beginTransaction();

        if(isset($data_request['variant_id']) && !empty($data_request['variant_id'])){
            $variant = Variant::where('id', $data_request['variant_id'])->where('product_id', isset($data_request['product_id']) ? $data_request['product_id'] : $check_data->product_id)->first();
            if(is_null($variant)) throw new ApplicationException(trans('error.variant_not_found_in_products', ['product_name' => $variant->products->name]));
        }

        if (isset($data_request['image'])) {
            if(Storage::disk('google')->exists('images/' . $check_data->image)) Storage::disk('google')->delete('images/' . $check_data->image);

            if(Storage::disk('google')->exists('images/' . 'thumbnails/' . $check_data->image)) Storage::disk('google')->delete('images/' . 'thumbnails/' . $check_data->image);

            $image = $data_request['image'];
            $image_name = time() . '.' . $image->getClientOriginalExtension();
            Storage::disk('google')->put('images/' . $image_name, file_get_contents($image));
            $img = Image::make($image);
            $img_thumb = $img->crop(5, 5);
            $img_thumb = $img_thumb->stream()->detach();
            Storage::disk('google')->put('images/thumbnails/' . $image_name, $img_thumb);
            $check_data->image = $image_name;
        }

        if(isset($data_request['variant_id'])) {
            if($data_request['variant_id'] == '') $variant_id = null;
            else $variant_id = $data_request['variant_id'];
        } else {
            $variant_id = $check_data->variant_id;
        }

        $check_data->product_id = $data_request['product_id'] ?? $check_data->product_id;
        $check_data->variant_id = $variant_id;
        $check_data->save();

        DB::commit();

        return $this->repository->getSingleData($locale, $id);
    }

    public function delete($locale, $id)
    {
        $data = $this->repository->getSingleData($locale, $id);

        DB::beginTransaction();
        if(Storage::disk('google')->exists('images/' . $data->image)) {
            Storage::disk('google')->delete('images/' . $data->image);
        }
        if(Storage::disk('google')->exists('images/' . 'thumbnails/' . $data->image)) {
            Storage::disk('google')->delete('images/' . 'thumbnails/' . $data->image);
        }
        $result = $data->delete();
        DB::commit();

        return $result;
    }
}
