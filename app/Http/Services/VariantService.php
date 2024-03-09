<?php

namespace App\Http\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Http\Models\Variant;
use App\Http\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use App\Exceptions\ApplicationException;
use App\Http\Repositories\VariantRepository;

class VariantService extends BaseService
{
    private $model, $repository;
    
    public function __construct(Variant $model, VariantRepository $repository)
    {
        $this->model = $model;
        $this->repository = $repository;
    }

    public function getIndexData($locale, $data)
    {
        $search = [
            'product_id' => 'product_id',
            'name' => 'name',
            'quantity' => 'quantity',
            'point' => 'point',
            'weight' => 'weight',
        ];

        $search_column = [
            'id' => 'id',
            'product_id' => 'product_id',
            'name' => 'name',
            'quantity' => 'quantity',
            'point' => 'point',
            'weight' => 'weight',
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

    public function getSingleDataBySlug($locale, $slug)
    {
        return $this->repository->getSingleDataBySlug($locale, $slug);
    }

    public function store($locale, $data)
    {
        $data_request = Arr::only($data, [
            'product_id',
            'name',
            'slug',
            'point',
            'weight',
            'quantity',
        ]);

        $this->repository->validate($data_request, [
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
            $product = Product::find($data_request['product_id']);
            $data_request['slug'] = $product->slug . '+' . Str::slug($data_request['name']);
            if($product->variants->count() > 0){
                $quantity = $product->quantity + $data_request['quantity'];
                $point = (min($product->variants->pluck('point')->toArray()) > (int) $data_request['point']) ? (int) $data_request['point'] : min($product->variants->pluck('point')->toArray());
                $weight = (min($product->variants->pluck('weight')->toArray()) > (int) $data_request['weight']) ? (int) $data_request['weight'] : min($product->variants->pluck('weight')->toArray());
            } else {
                $quantity = $data_request['quantity'];
                $point = $data_request['point'];
                $weight = $data_request['weight'];
            }
            $product->update([
                'point' => $point,
                'weight' => $weight,
                'quantity' => $quantity,
            ]);
            $result = $this->model->create($data_request);
            DB::commit();
        } catch (QueryException $e) {
            DB::rollBack();
            throw new ApplicationException(json_encode([$e->getMessage()]));
        }

        return $this->repository->getSingleData($locale, $result->id);
    }

    public function update($locale, $id, $data)
    {
        $check_data = $this->repository->getSingleData($locale, $id);

        $data = array_merge([
            'product_id' => $check_data->product_id,
            'name' => $check_data->name,
            'slug' => $check_data->slug,
            'point' => $check_data->point,
            'weight' => $check_data->weight,
            'quantity' => $check_data->quantity,
        ], $data);

        $data_request = Arr::only($data, [
            'product_id',
            'name',
            'slug',
            'point',
            'weight',
            'quantity',
        ]);

        $this->repository->validate($data_request, [
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
            ]
        );

        try {
            DB::beginTransaction();
            $product = Product::find($check_data->product_id);
            $data_request['slug'] = $product->slug . '+' . Str::slug($data_request['name']);
            $check_data->update($data_request);
            $weight = min($check_data->where('product_id', $data_request['product_id'])->pluck('weight')->toArray());
            $point = min($check_data->where('product_id', $data_request['product_id'])->pluck('point')->toArray());
            $quantity = array_sum($check_data->where('product_id', $data_request['product_id'])->pluck('quantity')->toArray());
            $product->update([
                'point' => $point,
                'weight' => $weight,
                'quantity' => $quantity,
            ]);
            DB::commit();
        } catch (QueryException $e) {
            DB::rollback();
            throw new ApplicationException(json_encode([$e->getMessage()]));
        }

        return $this->repository->getSingleData($locale, $id);
    }

    public function delete($locale, $id)
    {
        $check_data = $this->repository->getSingleData($locale, $id);
        
        try {
            DB::beginTransaction();
            $result = $check_data->delete();
            $product = Product::with('variants')->find($check_data->product_id);
            $variants = $product->variants->where('id', '!=', $id);
            $min_point = $variants->min('point');
            $min_weight = $variants->min('weight');
            $product->update([
                'point' => $min_point ?? 0,
                'weight' => $min_weight ?? 0,
                'quantity' => $product->quantity - $check_data->quantity,
            ]);
            DB::commit();
        } catch (QueryException $e) {
            DB::rollback();
            throw new ApplicationException(json_encode([$e->getMessage()]));
        }

        return $result;
    }
}
