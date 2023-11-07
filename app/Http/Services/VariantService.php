<?php

namespace App\Http\Services;

use Illuminate\Support\Arr;
use App\Http\Models\Variant;
use App\Http\Models\ItemGift;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
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
            'item_gift_id' => 'item_gift_id',
            'variant_name' => 'variant_name',
            'variant_quantity' => 'variant_quantity',
            'variant_point' => 'variant_point',
            'variant_weight' => 'variant_weight',
        ];

        $search_column = [
            'id' => 'id',
            'item_gift_id' => 'item_gift_id',
            'variant_name' => 'variant_name',
            'variant_quantity' => 'variant_quantity',
            'variant_point' => 'variant_point',
            'variant_weight' => 'variant_weight',
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
            'variant_name',
            'variant_point',
            'variant_weight',
            'variant_quantity',
        ]);

        $this->repository->validate($data_request, [
                'item_gift_id' => [
                    'required',
                    'exists:item_gifts,id',
                ],
                'variant_name' => [
                    'required',
                    'string',
                ],
                'variant_point' => [
                    'required',
                    'numeric',
                ],
                'variant_weight' => [
                    'required',
                    'numeric',
                ],
                'variant_quantity' => [
                    'required',
                    'numeric',
                ],
            ]
        );

        try {
            DB::beginTransaction();
            $item_gift = ItemGift::find($data_request['item_gift_id']);
            if($item_gift->variants->count() > 0){
                $quantity = $item_gift->item_gift_quantity + $data_request['variant_quantity'];
                $point = (min($item_gift->variants->pluck('variant_point')->toArray()) > (int) $data_request['variant_point']) ? (int) $data_request['variant_point'] : min($item_gift->variants->pluck('variant_point')->toArray());
                $weight = (min($item_gift->variants->pluck('variant_weight')->toArray()) > (int) $data_request['variant_weight']) ? (int) $data_request['variant_weight'] : min($item_gift->variants->pluck('variant_weight')->toArray());
            } else {
                $quantity = $data_request['variant_quantity'];
                $point = $data_request['variant_point'];
                $weight = $data_request['variant_weight'];
            }
            $item_gift->update([
                'item_gift_point' => $point,
                'item_gift_weight' => $weight,
                'item_gift_quantity' => $quantity,
            ]);
            $result = $this->model->create($data_request);
            DB::commit();
        } catch (QueryException $e) {
            DB::rollBack();
        }

        return $this->repository->getSingleData($locale, $result->id);
    }

    public function update($locale, $id, $data)
    {
        $check_data = $this->repository->getSingleData($locale, $id);

        $data = array_merge([
            'item_gift_id' => $check_data->item_gift_id,
            'variant_name' => $check_data->variant_name,
            'variant_point' => $check_data->variant_point,
            'variant_weight' => $check_data->variant_weight,
            'variant_quantity' => $check_data->variant_quantity,
        ], $data);

        $data_request = Arr::only($data, [
            'item_gift_id',
            'variant_name',
            'variant_point',
            'variant_weight',
            'variant_quantity',
        ]);

        $this->repository->validate($data_request, [
                'item_gift_id' => [
                    'exists:item_gifts,id',
                ],
                'variant_name' => [
                    'string',
                ],
                'variant_point' => [
                    'numeric',
                ],
                'variant_weight' => [
                    'numeric',
                ],
                'variant_quantity' => [
                    'numeric',
                ],
            ]
        );

        try {
            DB::beginTransaction();
            $item_gift = ItemGift::find($check_data->item_gift_id);
            $check_data->update($data_request);
            $weight = min($check_data->where('item_gift_id', $data_request['item_gift_id'])->pluck('variant_weight')->toArray());
            $point = min($check_data->where('item_gift_id', $data_request['item_gift_id'])->pluck('variant_point')->toArray());
            $quantity = array_sum($check_data->where('item_gift_id', $data_request['item_gift_id'])->pluck('variant_quantity')->toArray());
            $item_gift->update([
                'item_gift_point' => $point,
                'item_gift_weight' => $weight,
                'item_gift_quantity' => $quantity,
            ]);
            DB::commit();
        } catch (QueryException $e) {
            DB::rollback();
        }

        return $this->repository->getSingleData($locale, $id);
    }

    public function delete($locale, $id)
    {
        $check_data = $this->repository->getSingleData($locale, $id);
        
        try {
            DB::beginTransaction();
            $result = $check_data->delete();
            $item_gift = ItemGift::with('variants')->find($check_data->item_gift_id);
            $variants = $item_gift->variants->where('id', '!=', $id);
            $min_variant_point = $variants->min('variant_point');
            $min_variant_weight = $variants->min('variant_weight');
            $item_gift->update([
                'item_gift_point' => $min_variant_point ?? 0,
                'item_gift_weight' => $min_variant_weight ?? 0,
                'item_gift_quantity' => $item_gift->item_gift_quantity - $check_data->variant_quantity,
            ]);
            DB::commit();
        } catch (QueryException $e) {
            DB::rollback();
        }

        return $result;
    }
}
