<?php

namespace App\Http\Services;

use Image;
use App\Http\Models\Review;
use Illuminate\Support\Arr;
use App\Http\Models\ReviewFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Repositories\OrderRepository;
use App\Http\Repositories\ReviewRepository;

class ReviewService extends BaseService
{
    private $model, $repository, $order_repository;
    
    public function __construct(Review $model, ReviewRepository $repository, OrderRepository $order_repository)
    {
        $this->model = $model;
        $this->repository = $repository;
        $this->order_repository = $order_repository;
    }

    public function getIndexData($locale, $data)
    {
        $search = [
            'user_id' => 'user_id',
            'order_id' => 'order_id',
            'product_id' => 'product_id',
            'text' => 'text',
            'rating' => 'rating',
            'date' => 'date',
            'has_files' => 'has_files',
        ];

        $search_column = [
            'id' => 'id',
            'user_id' => 'user_id',
            'order_id' => 'order_id',
            'product_id' => 'product_id',
            'text' => 'text',
            'rating' => 'rating',
            'date' => 'date',
            'has_files' => 'has_files',
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
            'order_id',
            'product_id',
            'text',
            'rating',
            'file',
        ]);

        $this->repository->validate($data_request, [
                'order_id' => [
                    'required',
                    'exists:orders,id',
                ],
                'product_id' => [
                    'required',
                    'exists:products,id',
                ],
                'text' => [
                    'required'
                ],
                'rating' => [
                    'required',
                    'numeric',
                    'between:0.5,5'
                ],
                'file' => [
                    'nullable',
                    'array',
                ],
                'file.*' => [
                    'nullable',
                    'max:10000',
                    'mimes:jpg,png,mp4,mov',
                ],
            ]
        );

        DB::beginTransaction();

        $user = auth()->user();
        $order = $this->order_repository->getSingleData($locale, $data_request['order_id']);

        if ($order->status != 'shipped' && $order->status != 'success' && $order->payment_logs == null) {
            return response()->json([
                'error' => [
                    'message' => trans('error.order_not_completed', ['id' => $data_request['order_id']]),
                    'status_code' => 422,
                    'error' => 1
                ]
            ], 422);
        }

        $check_rating = $this->repository->getDataByUserOrderAndProduct($locale, $user->id, $data_request['order_id'], $data_request['product_id']);

        if (isset($check_rating)) {
            return response()->json([
                'error' => [
                    'message' => trans('error.already_reviews', ['order_id' => $data_request['order_id'], 'product_id' => $data_request['product_id']]),
                    'status_code' => 409,
                    'error' => 1
                ]
            ], 409);
        }

        $result = Review::create([
            'user_id' => $user->id,
            'order_id' => $data_request['order_id'],
            'product_id' => $data_request['product_id'],
            'text' => $data_request['text'],
            'rating' => rounded_rating($data_request['rating']),
            'date' => date('Y-m-d'),
        ]);

        if(isset($data_request['file'])){
            foreach ($data_request['file'] as $file) {
                $file_name = time() . '.' . $file->getClientOriginalExtension();
                Storage::disk('s3')->put('files/reviews/' . $file_name, file_get_contents($file));
                ReviewFile::create([
                    'review_id' => $result->id,
                    'file' => $file_name,
                ]);
            }
        }

        DB::commit();

        return response()->json([
            'message' => trans('all.success_reviews'),
            'status_code' => 200,
            'error' => 0,
        ], 200);
    }

    public function storeBulk($locale, $data)
    {
        $data_request = $data;

        $this->repository->validate($data_request, [
            'order_id' => [
                'required',
                'array',
            ],
            'order_id.*' => [
                'required',
                'exists:orders,id'
            ],
            'product_id' => [
                'required',
                'array',
            ],
            'product_id.*' => [
                'required',
                'exists:products,id',
            ],
            'text' => [
                'required',
                'array',
            ],
            'text.*' => [
                'required'
            ],
            'rating' => [
                'required',
                'array',
            ],
            'rating.*' => [
                'required',
                'numeric',
                'between:0.5,5'
            ],
            'file.*' => [
                'nullable',
                'array',
            ],
            'file.*.*' => [
                'nullable',
                'file',
                'mimes:jpg,png,mp4,mov',
                'max:10000',
            ],
        ]);

        DB::beginTransaction();

        $count_products = count($data_request['product_id']);

        for ($i = 0; $i < $count_products; $i++) {

            $user = auth()->user();
            $order = $this->order_repository->getSingleData($locale, $data_request['order_id'][$i]);

            if ($order->status != 'shipped' && $order->status != 'success' && $order->payment_logs == null) {
                return response()->json([
                    'error' => [
                        'message' => trans('error.order_not_completed', ['id' => $data_request['order_id'][$i]]),
                        'status_code' => 422,
                        'error' => 1
                    ]
                ], 422);
            }

            $check_rating = $this->repository->getDataByUserOrderAndProduct($locale, $user->id, $data_request['order_id'][$i], $data_request['product_id'][$i]);

            if (isset($check_rating)) {
                return response()->json([
                    'error' => [
                        'message' => trans('error.already_reviews', ['order_id' => $data_request['order_id'][$i], 'product_id' => $data_request['product_id'][$i]]),
                        'status_code' => 409,
                        'error' => 1
                    ]
                ], 409);
            }

            $result = Review::create([
                'user_id' => $user->id,
                'order_id' => $data_request['order_id'][$i],
                'product_id' => $data_request['product_id'][$i],
                'text' => $data_request['text'][$i],
                'rating' => rounded_rating($data_request['rating'][$i]),
                'date' => date('Y-m-d'),
            ]);

            if(isset($data_request['file'])){
                foreach ($data_request['file'][$i] as $file) {
                    $file_name = time() . '.' . $file->getClientOriginalExtension();
                    Storage::disk('s3')->put('files/reviews/' . $file_name, file_get_contents($file));
                    ReviewFile::create([
                        'review_id' => $result->id,
                        'file' => $file_name,
                    ]);
                }
            }
        }

        DB::commit();

        return response()->json([
            'message' => trans('all.success_reviews'),
            'status_code' => 200,
            'error' => 0,
        ], 200);
    }

    public function update($locale, $id, $data)
    {
        $check_data = $this->repository->getSingleData($locale, $id);

        $data = array_merge([
            'user_id' => $check_data->user_id,
            'order_id' => $check_data->order_id,
            'product_id' => $check_data->product_id,
            'text' => $check_data->text,
            'rating' => $check_data->rating,
        ], $data);

        $data_request = Arr::only($data, [
            'text',
            'rating',
        ]);

        $this->repository->validate($data_request, [
                'text' => [
                    'string'
                ],
                'rating' => [
                    'numeric',
                    'between:0.5,5'
                ],
                'file' => [
                    'nullable',
                    'array',
                ],
            ]
        );

        DB::beginTransaction();
        $data_request['rating'] = rounded_rating($data_request['rating']);
        $check_data->update($data_request);
        DB::commit();

        return $this->repository->getSingleData($locale, $id);
    }

    public function delete($locale, $id)
    {
        $check_data = $this->repository->getSingleData($locale, $id);
        DB::beginTransaction();
        foreach($check_data->files as $file) {
            if(Storage::disk('s3')->exists('files/reviews/' . $file->file)) {
                Storage::disk('s3')->delete('files/reviews/' . $file->file);
            }
        }
        $result = $check_data->delete();
        DB::commit();

        return $result;
    }
}
