<?php

namespace App\Http\Services;

use Image;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Http\Models\ItemGift;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Repositories\ItemGiftRepository;

class ItemGiftService extends BaseService
{
    private $model, $repository;
    
    public function __construct(ItemGift $model, ItemGiftRepository $repository)
    {
        $this->model = $model;
        $this->repository = $repository;
    }

    public function getIndexData($locale, $data)
    {
        $search = [
            'item_gift_name' => 'item_gift_name',
            'category_id' => 'category_id',
            'brand_id' => 'brand_id',
            'item_gift_slug' => 'item_gift_slug',
            'item_gift_weight' => 'item_gift_weight',
            'item_gift_quantity' => 'item_gift_quantity',
            'item_gift_point' => 'item_gift_point',
            'total_rating' => 'total_rating',
            'total_redeem' => 'total_redeem',
        ];

        $search_column = [
            'id' => 'id',
            'item_gift_name' => 'item_gift_name',
            'category_id' => 'category_id',
            'brand_id' => 'brand_id',
            'item_gift_slug' => 'item_gift_slug',
            'item_gift_quantity' => 'item_gift_quantity',
            'item_gift_point' => 'item_gift_point',
            'item_gift_weight' => 'item_gift_weight',
            'total_rating' => 'total_rating',
            'total_redeem' => 'total_redeem',
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

    public function getDataByCategory($locale, $category)
    {
        $search = [
            'item_gift_name' => 'item_gift_name',
            'category_id' => 'category_id',
            'brand_id' => 'brand_id',
            'item_gift_slug' => 'item_gift_slug',
            'item_gift_weight' => 'item_gift_weight',
            'item_gift_quantity' => 'item_gift_quantity',
            'item_gift_point' => 'item_gift_point',
            'total_rating' => 'total_rating',
            'total_redeem' => 'total_redeem',
        ];

        $search_column = [
            'id' => 'id',
            'item_gift_name' => 'item_gift_name',
            'category_id' => 'category_id',
            'brand_id' => 'brand_id',
            'item_gift_slug' => 'item_gift_slug',
            'item_gift_quantity' => 'item_gift_quantity',
            'item_gift_point' => 'item_gift_point',
            'item_gift_weight' => 'item_gift_weight',
            'total_rating' => 'total_rating',
            'total_redeem' => 'total_redeem',
        ];

        $sortable_and_searchable_column = [
            'search'        => $search,
            'search_column' => $search_column,
            'sort_column'   => array_merge($search, $search_column),
        ];

        return $this->repository->getSingleDataByCategory($locale, $sortable_and_searchable_column, $category);
    }

    public function getDataByBrand($locale, $brand)
    {
        $search = [
            'item_gift_name' => 'item_gift_name',
            'category_id' => 'category_id',
            'brand_id' => 'brand_id',
            'item_gift_slug' => 'item_gift_slug',
            'item_gift_weight' => 'item_gift_weight',
            'item_gift_quantity' => 'item_gift_quantity',
            'item_gift_point' => 'item_gift_point',
            'total_rating' => 'total_rating',
            'total_redeem' => 'total_redeem',
        ];

        $search_column = [
            'id' => 'id',
            'item_gift_name' => 'item_gift_name',
            'category_id' => 'category_id',
            'brand_id' => 'brand_id',
            'item_gift_slug' => 'item_gift_slug',
            'item_gift_quantity' => 'item_gift_quantity',
            'item_gift_point' => 'item_gift_point',
            'item_gift_weight' => 'item_gift_weight',
            'total_rating' => 'total_rating',
            'total_redeem' => 'total_redeem',
        ];

        $sortable_and_searchable_column = [
            'search'        => $search,
            'search_column' => $search_column,
            'sort_column'   => array_merge($search, $search_column),
        ];
        
        return $this->repository->getDataByBrand($locale, $sortable_and_searchable_column, $brand);
    }

    public function getDataByUserRecomendation($locale)
    {
        $search = [
            'item_gift_name' => 'item_gift_name',
            'category_id' => 'category_id',
            'brand_id' => 'brand_id',
            'item_gift_slug' => 'item_gift_slug',
            'item_gift_weight' => 'item_gift_weight',
            'item_gift_quantity' => 'item_gift_quantity',
            'item_gift_point' => 'item_gift_point',
            'total_rating' => 'total_rating',
            'total_redeem' => 'total_redeem',
        ];

        $search_column = [
            'id' => 'id',
            'item_gift_name' => 'item_gift_name',
            'category_id' => 'category_id',
            'brand_id' => 'brand_id',
            'item_gift_slug' => 'item_gift_slug',
            'item_gift_quantity' => 'item_gift_quantity',
            'item_gift_point' => 'item_gift_point',
            'item_gift_weight' => 'item_gift_weight',
            'total_rating' => 'total_rating',
            'total_redeem' => 'total_redeem',
        ];

        $sortable_and_searchable_column = [
            'search'        => $search,
            'search_column' => $search_column,
            'sort_column'   => array_merge($search, $search_column),
        ];
        
        return $this->repository->getDataByUserRecomendation($locale, $sortable_and_searchable_column);
    }

    public function store($locale, $data)
    {
        $data_request = Arr::only($data, [
            'item_gift_name',
            'category_id',
            'brand_id',
            'item_gift_description',
            'item_gift_point',
            'item_gift_weight',
            'item_gift_quantity',
            'item_gift_images',
            'item_gift_spesification',
        ]);

        $this->repository->validate($data_request, [
                'item_gift_name' => [
                    'required',
                    'unique:item_gifts,item_gift_name',
                ],
                'category_id' => [
                    'nullable',
                    'exists:categories,id',
                ],
                'brand_id' => [
                    'nullable',
                    'exists:brands,id',
                ],
                'item_gift_description' => [
                    'required',
                    'string',
                ],
                'item_gift_spesification' => [
                    'nullable',
                    'array',
                ],
                'item_gift_spesification.*.key' => [
                    'string',
                    'required_with:item_gift_spesification.*.value',
                ],
                'item_gift_spesification.*.value' => [
                    'string',
                    'required_with:item_gift_spesification.*.key',
                ],
                'item_gift_point' => [
                    'nullable',
                    'numeric',
                ],
                'item_gift_weight' => [
                    'nullable',
                    'numeric',
                ],
                'item_gift_quantity' => [
                    'nullable',
                    'numeric'
                ],
                'item_gift_images' => [
                    'required',
                    'array'
                ],
                'item_gift_images.*' => [
                    'required',
                    'max:1000',
                    'mimes:jpg,png',
                ],
            ]
        );
        DB::beginTransaction();
        $data_request['item_gift_code'] = Str::random(15);
        $data_request['item_gift_slug'] = Str::slug($data_request['item_gift_name']);
        $data_request['item_gift_point'] = $data_request['item_gift_point'] ?? null;
        $data_request['item_gift_weight'] = $data_request['item_gift_weight'] ?? null;
        $data_request['item_gift_quantity'] = $data_request['item_gift_quantity'] ?? null;
        $data_request['item_gift_spesification'] = (isset($data_request['item_gift_spesification'])) ? json_encode($data_request['item_gift_spesification']) : null;
        $result = $this->model->create($data_request);
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
        DB::commit();

        return $this->repository->getSingleData($locale, $result->id);
    }

    public function update($locale, $id, $data)
    {
        $check_data = $this->repository->getSingleData($locale, $id);

        $data = array_merge([
            'item_gift_name' => $check_data->item_gift_name,
            'category_id' => $check_data->category_id,
            'brand_id' => $check_data->brand_id,
            'item_gift_slug' => $check_data->item_gift_slug,
            'item_gift_description' => $check_data->item_gift_description,
            'item_gift_point' => $check_data->item_gift_point,
            'item_gift_weight' => $check_data->item_gift_weight,
            'item_gift_quantity' => $check_data->item_gift_quantity,
            'item_gift_spesification' => json_decode($check_data->item_gift_spesification),
        ], $data);

        $data_request = Arr::only($data, [
            'item_gift_name',
            'category_id',
            'brand_id',
            'item_gift_slug',
            'item_gift_description',
            'item_gift_point',
            'item_gift_weight',
            'item_gift_quantity',
            'item_gift_spesification',
        ]);

        $this->repository->validate($data_request, [
                'item_gift_name' => [
                    'string',
                    'unique:item_gifts,item_gift_name,' . $id,
                ],
                'category_id' => [
                    'nullable',
                    'exists:categories,id',
                ],
                'brand_id' => [
                    'nullable',
                    'exists:brands,id',
                ],
                'item_gift_description' => [
                    'string',
                ],
                'item_gift_spesification' => [
                    'nullable',
                    'array',
                ],
                'item_gift_spesification.*.key' => [
                    'string',
                    'required_with:item_gift_spesification.*.value',
                ],
                'item_gift_spesification.*.value' => [
                    'string',
                    'required_with:item_gift_spesification.*.key',
                ],
                'item_gift_point' => [
                    'numeric',
                ],
                'item_gift_weight' => [
                    'nullable',
                    'numeric',
                ],
                'item_gift_quantity' => [
                    'numeric',
                ],
            ]
        );

        DB::beginTransaction();
        $data_request['item_gift_slug'] = Str::slug($data_request['item_gift_name']);
        $data_request['item_gift_point'] = ($check_data->variants->count() > 0) ? min($check_data->variants->pluck('variant_point')->toArray()) : $data_request['item_gift_point'];
        $data_request['item_gift_spesification'] = (isset($data_request['item_gift_spesification'])) ? json_encode($data_request['item_gift_spesification']) : null;
        $check_data->update($data_request);
        DB::commit();

        return $this->repository->getSingleData($locale, $id);
    }

    public function delete($locale, $id)
    {
        $check_data = $this->repository->getSingleData($locale, $id);
        
        DB::beginTransaction();
        foreach($check_data->item_gift_images as $image) {
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
