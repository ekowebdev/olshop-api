<?php

namespace App\Http\Services;

use App\Http\Models\Review;
use Illuminate\Support\Arr;
use App\Http\Models\Product;
use App\Http\Models\ReviewFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Exceptions\ConflictException;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Storage;
use App\Exceptions\ApplicationException;
use App\Http\Repositories\OrderRepository;
use App\Http\Repositories\ReviewRepository;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class ReviewService extends BaseService
{
    private $model, $modelReviewFile, $modelProduct, $repository, $orderRepository;

    public function __construct(Review $model, ReviewFile $modelReviewFile, Product $modelProduct, ReviewRepository $repository, OrderRepository $orderRepository)
    {
        $this->model = $model;
        $this->modelReviewFile = $modelReviewFile;
        $this->modelProduct = $modelProduct;
        $this->repository = $repository;
        $this->orderRepository = $orderRepository;
    }

    public function index($locale, $data)
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

        $searchColumn = [
            'id' => 'id',
            'user_id' => 'user_id',
            'order_id' => 'order_id',
            'product_id' => 'product_id',
            'text' => 'text',
            'rating' => 'rating',
            'date' => 'date',
            'has_files' => 'has_files',
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
            'order_id',
            'product_id',
            'text',
            'rating',
            'file',
        ]);

        $this->repository->validate($request, [
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
                'mimes:jpg,png',
            ],
        ]);

        DB::beginTransaction();

        $user = auth()->user();
        $order = $this->orderRepository->getSingleData($locale, $request['order_id']);
        $product = $this->modelProduct->find($request['product_id']);

        if ($order->status != 'shipped' && $order->status != 'success' && $order->payment_logs == null) throw new ApplicationException(trans('error.order_not_completed', ['order_code' => $order->code]));

        $checkRating = $this->repository->getDataByUserOrderAndProduct($locale, $user->id, $request['order_id'], $request['product_id']);
        if (isset($checkRating)) throw new ConflictException;(trans('error.already_reviews', ['order_code' => $order->code, 'product_name' => $product->name]));

        $result = $this->model->create([
            'user_id' => $user->id,
            'order_id' => $request['order_id'],
            'product_id' => $request['product_id'],
            'text' => $request['text'],
            'rating' => roundedRating($request['rating']),
            'date' => date('Y-m-d'),
        ]);

        if(is_array(Request::file('file'))){
            foreach (Request::file('file') as $file) {
                $fileName = uploadImagesToCloudinary($file, 'reviews');
                $this->modelReviewFile->create([
                    'review_id' => $result->id,
                    'file' => $fileName,
                ]);
            }
        }

        DB::commit();

        return response()->api(trans('all.success_reviews'));
    }

    public function storeBulk($locale, $data)
    {
        $request = $data;

        $this->repository->validate($request, [
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

        // $countProducts = count($request['product_id']);

        // for ($i = 0; $i < $countProducts; $i++) {
        //     $user = auth()->user();

        //     $order = $this->orderRepository->getSingleData($locale, $request['order_id'][$i]);
        //     if($order->status != 'shipped' && $order->status != 'success' && $order->payment_logs == null) throw new ApplicationException(trans('error.order_not_completed', ['order_code' => $order->code[$i]]));

        //     $checkRating = $this->repository->getDataByUserOrderAndProduct($locale, $user->id, $request['order_id'][$i], $request['product_id'][$i]);
        //     if(isset($checkRating)) throw new ConflictException(trans('error.already_reviews', ['order_code' => $order->code[$i], 'product_name' => $product->name[$i]]));

        //     $result = $this->model->create([
        //         'user_id' => $user->id,
        //         'order_id' => $request['order_id'][$i],
        //         'product_id' => $request['product_id'][$i],
        //         'text' => $request['text'][$i],
        //         'rating' => roundedRating($request['rating'][$i]),
        //         'date' => date('Y-m-d'),
        //     ]);

        //     if(isset($request['file'])){
        //         foreach ($request['file'][$i] as $file) {
        //             $fileName = uploadImagesToCloudinary($file, 'reviews');
        //             $this->modelReviewFile->create([
        //                 'review_id' => $result->id,
        //                 'file' => $fileName,
        //             ]);
        //         }
        //     }
        // }

        $user = auth()->user();
        $products = collect($request['product_id']);

        $products->chunk(100)->each(function (Collection $productChunk) use ($request, $user, $locale) {
            DB::transaction(function () use ($productChunk, $request, $user, $locale) {
                foreach ($productChunk as $index => $productId) {
                    $orderId = $request['order_id'][$index];

                    $order = $this->orderRepository->getSingleData($locale, $orderId);
                    if ($order->status != 'shipped' && $order->status != 'success' && $order->payment_logs == null) {
                        throw new ApplicationException(trans('error.order_not_completed', ['order_code' => $order->code]));
                    }

                    $checkRating = $this->repository->getDataByUserOrderAndProduct($locale, $user->id, $orderId, $productId);
                    if (isset($checkRating)) {
                        throw new ConflictException(trans('error.already_reviews', ['order_code' => $order->code, 'product_name' => $product->name]));
                    }

                    $result = $this->model->create([
                        'user_id' => $user->id,
                        'order_id' => $orderId,
                        'product_id' => $productId,
                        'text' => $request['text'][$index],
                        'rating' => roundedRating($request['rating'][$index]),
                        'date' => date('Y-m-d'),
                    ]);

                    if (isset($request['file'][$index])) {
                        foreach ($request['file'][$index] as $file) {
                            $fileName = uploadImagesToCloudinary($file, 'reviews');
                            $this->modelReviewFile->create([
                                'review_id' => $result->id,
                                'file' => $fileName,
                            ]);
                        }
                    }
                }
            });
        });

        DB::commit();

        return response()->api(trans('all.success_reviews'));
    }

    public function update($locale, $id, $data)
    {
        $checkData = $this->repository->getSingleData($locale, $id);

        $data = array_merge([
            'user_id' => $checkData->user_id,
            'order_id' => $checkData->order_id,
            'product_id' => $checkData->product_id,
            'text' => $checkData->text,
            'rating' => $checkData->rating,
        ], $data);

        $request = Arr::only($data, [
            'text',
            'rating',
        ]);

        $this->repository->validate($request, [
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
        ]);

        DB::beginTransaction();

        $request['rating'] = roundedRating($request['rating']);
        $checkData->update($request);

        DB::commit();

        return $this->repository->getSingleData($locale, $id);
    }

    public function delete($locale, $id)
    {
        $checkData = $this->repository->getSingleData($locale, $id);

        DB::beginTransaction();

        foreach($checkData->review_files as $file) {
            deleteImagesFromCloudinary($file->file, 'reviews');
        }

        $result = $checkData->delete();

        DB::commit();

        return $result;
    }
}
