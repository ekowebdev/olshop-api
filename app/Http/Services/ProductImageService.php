<?php

namespace App\Http\Services;

use Illuminate\Support\Arr;
use App\Http\Models\Variant;
use App\Http\Models\ProductImage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use App\Exceptions\ValidationException;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Storage;
use App\Exceptions\ApplicationException;
use App\Http\Repositories\ProductRepository;
use App\Http\Repositories\VariantRepository;
use App\Http\Repositories\ProductImageRepository;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;


class ProductImageService extends BaseService
{
    private $model, $modelVariant, $repository, $productRepository;

    public function __construct(ProductImage $model, Variant $modelVariant, ProductImageRepository $repository, ProductRepository $productRepository, VariantRepository $variantRepository)
    {
        $this->model = $model;
        $this->modelVariant = $modelVariant;
        $this->repository = $repository;
        $this->productRepository = $productRepository;
        $this->variantRepository = $variantRepository;
    }

    public function index($locale, $data)
    {
        $search = [
            'product_id' => 'product_id',
            'variant_id' => 'variant_id',
        ];

        $searchColumn = [
            'id' => 'id',
            'product_id' => 'product_id',
            'variant_id' => 'variant_id',
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

    public function store($locale, $data)
    {
        $request = Arr::only($data, [
            'product_id',
            'variant_id',
            'image',
            'is_primary',
        ]);

        $this->repository->validate($request, [
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

        if (isset($request['variant_id'])) {
            // $variant = $this->modelVariant->where('id', $request['variant_id'])->where('product_id', $request['product_id'])->first();
            $variant = $this->variantRepository->getSingleDataByIdAndProductId($request['variant_id'], $request['product_id']);
            if(is_null($variant)) throw new ValidationException(trans('error.variant_not_found_in_products', ['product_name' => $variant->products->name]));

            $existsVariant = $this->repository->getSingleDataByProductIdAndVariantId($request['product_id'], $request['variant_id']);
            if(!is_null($existsVariant)) throw new ValidationException(trans('error.exists_image_variant_products', ['product_name' => $variant->products->name, 'variant_name' => $variant->name]));
        }

        // $existingPrimary = $this->model->where('product_id', $request['product_id']);
        $existingPrimary = $this->repository->queryByProductId($checkData->product_id);
        $isPrimary = $request['is_primary'] ?? 0;

        if ($isPrimary) {
            if ($existingPrimary->count() > 0) {
                $existingPrimary->update(['is_primary' => 0]);
            }
        }

        if ($existingPrimary->count() == 0) {
            $isPrimary = 1;
        }

        $file = Request::file('image');

        $imageName = uploadImagesToCloudinary($file, 'products');

        $result = $this->model->create([
            'product_id' => $request['product_id'],
            'variant_id' => (isset($request['variant_id'])) ? $request['variant_id'] : null,
            'image' => $imageName,
            'is_primary' => $isPrimary,
        ]);

        if ($isPrimary) {
            $product = $this->productRepository->getSingleData($locale, $request['product_id']);
            $product->update([
                'main_image' => $imageName
            ]);
        }

        DB::commit();

        return $this->repository->getSingleData($locale, $result->id);
    }

    public function update($locale, $id, $data)
    {
        $checkData = $this->repository->getSingleData($locale, $id);

        $request = Arr::only($data, [
            'product_id',
            'variant_id',
            'image',
            'is_primary',
        ]);

        $this->repository->validate($request, [
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

        if (isset($request['variant_id']) && !empty($request['variant_id'])) {
            // $variant = $this->modelVariant->where('id', $request['variant_id'])->where('product_id', isset($request['product_id']) ? $request['product_id'] : $checkData->product_id)->first();
            $variant = $this->variantRepository->getSingleDataByIdAndProductId($request['variant_id'], isset($request['product_id']) ? $request['product_id'] : $checkData->product_id);
            if(is_null($variant)) throw new ValidationException(trans('error.variant_not_found_in_products', ['product_name' => $variant->products->name]));
        }

        // $existingPrimary = $this->model->where('product_id', $checkData->product_id);
        $existingPrimary = $this->repository->queryByProductId($checkData->product_id);
        $isPrimary = $request['is_primary'] ?? 0;

        if ($isPrimary) {
            if ($existingPrimary->count() > 0) {
                $existingPrimary->update(['is_primary' => 0]);
            }
        }

        if ($existingPrimary->count() == 0) {
            $isPrimary = 1;
        }

        if (isset($request['image'])) {
            $file = Request::file('image');

            if ($checkData->image) {
                deleteImagesFromCloudinary($checkData->image, 'products');
            }

            $imageName = uploadImagesToCloudinary($file, 'products');

            $checkData->image = $imageName;
        }

        if (isset($request['variant_id'])) {
            if ($request['variant_id'] == '') $variant_id = null;
            else $variant_id = $request['variant_id'];
        } else {
            $variant_id = $checkData->variant_id;
        }

        $checkData->product_id = $request['product_id'] ?? $checkData->product_id;
        $checkData->variant_id = $variant_id;
        $checkData->is_primary = $isPrimary;
        $checkData->save();

        if ($isPrimary) {
            $product = $this->productRepository->getSingleData($locale, $checkData->product_id);
            $product->update([
                'main_image' => $checkData->image
            ]);
        }

        DB::commit();

        return $this->repository->getSingleData($locale, $id);
    }

    public function delete($locale, $id)
    {
        $checkData = $this->repository->getSingleData($locale, $id);

        DB::beginTransaction();

        // $existingPrimary = $this->model->where('product_id', $checkData->product_id);
        $existingPrimary = $this->repository->queryByProductId($checkData->product_id);
        if ($existingPrimary->count() > 1) {
            if ($checkData->is_primary) {
                throw new ApplicationException(trans('error.cannot_delete_main_image'));
            }
        }

        $product = $this->productRepository->getSingleData($locale, $checkData->product_id);
        if ($checkData->image == $product->main_image) {
            $product->update(['main_image' => null]);
        }

        deleteImagesFromCloudinary($checkData->image, 'products');

        $result = $checkData->delete();

        DB::commit();

        return $result;
    }
}
