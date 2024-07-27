<?php

namespace App\Http\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Http\Models\Variant;
use App\Http\Models\Product;
use Illuminate\Support\Facades\DB;
use App\Exceptions\ApplicationException;
use App\Http\Repositories\VariantRepository;

class VariantService extends BaseService
{
    private $model, $modelProduct, $repository;

    public function __construct(Variant $model, Product $modelProduct, VariantRepository $repository)
    {
        $this->model = $model;
        $this->modelProduct = $modelProduct;
        $this->repository = $repository;
    }

    public function index($locale, $data)
    {
        $search = [
            'product_id' => 'product_id',
            'name' => 'name',
            'quantity' => 'quantity',
            'point' => 'point',
            'weight' => 'weight',
        ];

        $searchColumn = [
            'id' => 'id',
            'product_id' => 'product_id',
            'name' => 'name',
            'quantity' => 'quantity',
            'point' => 'point',
            'weight' => 'weight',
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

    public function showBySlug($locale, $slug)
    {
        return $this->repository->getSingleDataBySlug($locale, $slug);
    }

    public function store($locale, $data)
    {
        $request = Arr::only($data, [
            'product_id',
            'name',
            'slug',
            'point',
            'weight',
            'quantity',
        ]);

        $this->repository->validate($request, [
                'product_id' => [
                    'required',
                    'exists:products,id',
                ],
                'name' => [
                    'required',
                    'string',
                    'unique:variants,name',
                ],
                'point' => [
                    'required',
                    'numeric',
                ],
                'weight' => [
                    'required',
                    'numeric',
                ],
                'quantity' => [
                    'required',
                    'numeric',
                ],
            ]
        );

        try {
            DB::beginTransaction();

            $product = $this->modelProduct->find($request['product_id']);
            $request['slug'] = $product->slug . '-' . Str::slug($request['name']);

            if($product->variants->count() > 0){
                $quantity = $product->quantity + $request['quantity'];
                $point = (min($product->variants->pluck('point')->toArray()) > (int) $request['point']) ? (int) $request['point'] : min($product->variants->pluck('point')->toArray());
                $weight = (min($product->variants->pluck('weight')->toArray()) > (int) $request['weight']) ? (int) $request['weight'] : min($product->variants->pluck('weight')->toArray());
            } else {
                $quantity = $request['quantity'];
                $point = $request['point'];
                $weight = $request['weight'];
            }

            $product->update([
                'point' => $point,
                'weight' => $weight,
                'quantity' => $quantity,
            ]);

            $result = $this->model->create($request);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw new ApplicationException(json_encode([$e->getMessage()]));
        }

        return $this->repository->getSingleData($locale, $result->id);
    }

    public function update($locale, $id, $data)
    {
        $checkData = $this->repository->getSingleData($locale, $id);

        $data = array_merge([
            'product_id' => $checkData->product_id,
            'name' => $checkData->name,
            'slug' => $checkData->slug,
            'point' => $checkData->point,
            'weight' => $checkData->weight,
            'quantity' => $checkData->quantity,
        ], $data);

        $request = Arr::only($data, [
            'product_id',
            'name',
            'slug',
            'point',
            'weight',
            'quantity',
        ]);

        $this->repository->validate($request, [
            'product_id' => [
                'exists:products,id',
            ],
            'name' => [
                'string',
                'unique:variants,name,' . $id,
            ],
            'point' => [
                'numeric',
            ],
            'weight' => [
                'numeric',
            ],
            'quantity' => [
                'numeric',
            ],
        ]);

        try {
            DB::beginTransaction();

            $product = $this->modelProduct->find($checkData->product_id);
            $request['slug'] = $product->slug . '-' . Str::slug($request['name']);
            $checkData->update($request);
            $weight = min($checkData->where('product_id', $request['product_id'])->pluck('weight')->toArray());
            $point = min($checkData->where('product_id', $request['product_id'])->pluck('point')->toArray());
            $quantity = array_sum($checkData->where('product_id', $request['product_id'])->pluck('quantity')->toArray());
            $product->update([
                'point' => $point,
                'weight' => $weight,
                'quantity' => $quantity,
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw new ApplicationException(json_encode([$e->getMessage()]));
        }

        return $this->repository->getSingleData($locale, $id);
    }

    public function delete($locale, $id)
    {
        $checkData = $this->repository->getSingleData($locale, $id);

        try {
            DB::beginTransaction();

            $result = $checkData->delete();
            $product = $this->modelProduct->with('variants')->find($checkData->product_id);
            $variants = $product->variants->where('id', '!=', $id);
            $minPoint = $variants->min('point');
            $minWeight = $variants->min('weight');
            $product->update([
                'point' => $minPoint ?? 0,
                'weight' => $minWeight ?? 0,
                'quantity' => $product->quantity - $checkData->quantity,
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw new ApplicationException(json_encode([$e->getMessage()]));
        }

        return $result;
    }
}
