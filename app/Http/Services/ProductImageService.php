<?php

namespace App\Http\Services;

use Illuminate\Support\Arr;
use App\Http\Models\Variant;
use App\Http\Models\ProductImage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Storage;
use App\Exceptions\ValidationException;
use App\Exceptions\ApplicationException;
use App\Http\Repositories\ProductRepository;
use App\Http\Repositories\ProductImageRepository;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;


class ProductImageService extends BaseService
{
    private $model, $modelVariant, $repository, $repositoryProduct;

    public function __construct(ProductImage $model, Variant $modelVariant, ProductImageRepository $repository, ProductRepository $repositoryProduct)
    {
        $this->model = $model;
        $this->modelVariant = $modelVariant;
        $this->repository = $repository;
        $this->repositoryProduct = $repositoryProduct;
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
            'is_primary',
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
            'is_primary' => [
                'in:0,1',
            ],
        ]);

        DB::beginTransaction();

        if (isset($data_request['variant_id'])) {
            $variant = $this->modelVariant->where('id', $data_request['variant_id'])->where('product_id', $data_request['product_id'])->first();
            if(is_null($variant)) throw new ValidationException(trans('error.variant_not_found_in_products', ['product_name' => $variant->products->name]));

            $exists_variant = $this->repository->getSingleDataByProductVariant($locale, $data_request['product_id'], $data_request['variant_id']);
            if(!is_null($exists_variant)) throw new ValidationException(trans('error.exists_image_variant_products', ['product_name' => $variant->products->name, 'variant_name' => $variant->name]));
        }

        $existingPrimary = $this->model->where('product_id', $data_request['product_id']);
        $isPrimary = $data_request['is_primary'] ?? 0;

        if ($isPrimary) {
            if (count($existingPrimary->get()) > 0) {
                $existingPrimary->update(['is_primary' => 0]);
            }
        }

        if (count($existingPrimary->get()) == 0) {
            $isPrimary = 1;
        }

        $file = Request::file('image');

        $imageName = uploadImagesToCloudinary($file, 'products');

        $result = $this->model->create([
            'product_id' => $data_request['product_id'],
            'variant_id' => (isset($data_request['variant_id'])) ? $data_request['variant_id'] : null,
            'image' => $imageName,
            'is_primary' => $isPrimary,
        ]);

        if ($isPrimary) {
            $product = $this->repositoryProduct->getSingleData($locale, $data_request['product_id']);
            $product->update([
                'main_image' => $imageName
            ]);
        }

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
            'is_primary',
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
            'is_primary' => [
                'in:0,1',
            ],
        ]);

        DB::beginTransaction();

        if (isset($data_request['variant_id']) && !empty($data_request['variant_id'])) {
            $variant = $this->modelVariant->where('id', $data_request['variant_id'])->where('product_id', isset($data_request['product_id']) ? $data_request['product_id'] : $check_data->product_id)->first();
            if(is_null($variant)) throw new ValidationException(trans('error.variant_not_found_in_products', ['product_name' => $variant->products->name]));
        }

        $existingPrimary = $this->model->where('product_id', $check_data->product_id);
        $isPrimary = $data_request['is_primary'] ?? 0;

        if ($isPrimary) {
            if (count($existingPrimary->get()) > 0) {
                $existingPrimary->update(['is_primary' => 0]);
            }
        }

        if (count($existingPrimary->get()) == 0) {
            $isPrimary = 1;
        }

        if (isset($data_request['image'])) {
            $file = Request::file('image');

            if ($check_data->image) {
                deleteImagesFromCloudinary($check_data->image, 'products');
            }

            $imageName = uploadImagesToCloudinary($file, 'products');

            $check_data->image = $imageName;
        }

        if (isset($data_request['variant_id'])) {
            if ($data_request['variant_id'] == '') $variant_id = null;
            else $variant_id = $data_request['variant_id'];
        } else {
            $variant_id = $check_data->variant_id;
        }

        $check_data->product_id = $data_request['product_id'] ?? $check_data->product_id;
        $check_data->variant_id = $variant_id;
        $check_data->is_primary = $isPrimary;
        $check_data->save();

        if ($isPrimary) {
            $product = $this->repositoryProduct->getSingleData($locale, $check_data->product_id);
            $product->update([
                'main_image' => $check_data->image
            ]);
        }

        DB::commit();

        return $this->repository->getSingleData($locale, $id);
    }

    public function delete($locale, $id)
    {
        $check_data = $this->repository->getSingleData($locale, $id);

        DB::beginTransaction();
        $existingPrimary = $this->model->where('product_id', $check_data->product_id);
        if (count($existingPrimary->get()) > 1) {
            if ($check_data->is_primary) {
                throw new ApplicationException(trans('error.cannot_delete_main_image'));
            }
        }
        $product = $this->repositoryProduct->getSingleData($locale, $check_data->product_id);
        if ($check_data->image == $product->main_image) {
            $product->update([
                'main_image' => null
            ]);
        }
        deleteImagesFromCloudinary($check_data->image, 'products');
        $result = $check_data->delete();
        DB::commit();

        return $result;
    }
}
