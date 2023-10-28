<?php

namespace App\Http\Services;

use Image;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
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

        DB::beginTransaction();
        try {
            $item_gift = ItemGift::find($data_request['item_gift_id']);
            if($item_gift->variants->count() == 0){
                $quantity = $data_request['variant_quantity'];
                $total_point = $data_request['variant_point'];
                $total_weight = $data_request['variant_weight'];
            } else {
                $quantity = $item_gift->item_gift_quantity + $data_request['variant_quantity'];
                $total_point = (min($item_gift->variants->pluck('variant_point')->toArray()) > (int) $data_request['variant_point']) ? (int) $data_request['variant_point'] : min($item_gift->variants->pluck('variant_point')->toArray());
                $total_weight = (min($item_gift->variants->pluck('variant_weight')->toArray()) > (int) $data_request['variant_weight']) ? (int) $data_request['variant_weight'] : min($item_gift->variants->pluck('variant_weight')->toArray());
            }
            $item_gift->update([
                'item_gift_point' => $total_point,
                'item_gift_weight' => $total_weight,
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

        DB::beginTransaction();
        try {
            $item_gift = ItemGift::find($check_data->item_gift_id);
            $check_data->update($data_request);
            $total_weight = min($check_data->where('item_gift_id', $data_request['item_gift_id'])->pluck('variant_weight')->toArray());
            $total_point = min($check_data->where('item_gift_id', $data_request['item_gift_id'])->pluck('variant_point')->toArray());
            $qty_item_gift = $item_gift->item_gift_quantity - $check_data->variant_quantity;
            $qty_update = ($item_gift->variants->count() > 0) ? $qty_item_gift + $data_request['variant_quantity'] : $item_gift->item_gift_quantity;
            $item_gift->update([
                'item_gift_point' => $total_point,
                'item_gift_weight' => $total_weight,
                'item_gift_quantity' => $qty_update,
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
        
        DB::beginTransaction();
        $item_gift = ItemGift::find($check_data->item_gift_id);
        $qty_item_gift = $item_gift->item_gift_quantity - $check_data->variant_quantity;
        $filtered_variant = array_filter($item_gift->variants->toArray(), function ($item) use ($id) {
            return $item['id'] != intval($id);
        });
        $item_gift->update([
            'item_gift_point' => (!empty(array_column($filtered_variant, 'variant_point'))) ? min(array_column($filtered_variant, 'variant_point')) : 0,
            'item_gift_weight' => (!empty(array_column($filtered_variant, 'variant_weight'))) ? min(array_column($filtered_variant, 'variant_weight')) : 0,
            'item_gift_quantity' => $qty_item_gift,
        ]);
        $result = $check_data->delete();
        DB::commit();

        return $result;
    }
}
