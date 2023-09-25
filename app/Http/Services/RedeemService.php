<?php

namespace App\Http\Services;

use App\Http\Models\Redeem;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Http\Models\Variant;
use App\Http\Models\ItemGift;
use App\Http\Models\Shipping;
use App\Mail\RedeemConfirmation;
use Illuminate\Support\Facades\DB;
use App\Http\Models\RedeemItemGift;
use App\Http\Services\RedeemService;
use Illuminate\Support\Facades\Mail;
use App\Exceptions\ValidationException;
use Illuminate\Database\QueryException;
use App\Http\Services\RajaOngkirService;
use App\Http\Repositories\RedeemRepository;
use App\Jobs\SendEmailRedeemConfirmationJob;
use App\Http\Repositories\ItemGiftRepository;

class RedeemService extends BaseService
{
    private $model, $repository, $item_gift_repository, $rajaongkir_service;
    
    public function __construct(Redeem $model, RedeemRepository $repository, ItemGiftRepository $item_gift_repository, RajaOngkirService $rajaongkir_service)
    {
        $this->model = $model;
        $this->repository = $repository;
        $this->item_gift_repository = $item_gift_repository;
        $this->rajaongkir_service = $rajaongkir_service;
    }

    public function getIndexData($locale, $data)
    {
        $search = [
            'redeem_code' => 'redeem_code',
            'user_id' => 'user_id',
            'total_point' => 'total_point',
        ];

        $search_column = [
            'id' => 'id',
            'redeem_code' => 'redeem_code',
            'user_id' => 'user_id',
            'total_point' => 'total_point',
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

    public function redeem($locale, $id, $data)
    {
        $item_gift = $this->item_gift_repository->getSingleData($locale, $id);

        $data_request = Arr::only($data, [
            'variant_id',
            'redeem_quantity',
        ]);

        $this->item_gift_repository->validate($data_request, [
                'variant_id' => [
                    'exists:variants,id'
                ],
                'redeem_quantity' => [
                    'required',
                    'numeric',
                    'min:1',
                ],
            ]
        );

        DB::beginTransaction();
        try {
            $item_gift_variant = null;
            $variant_locked = false;
            $total_point = 0;
            $metadata_redeem_item_gifts = [];
            $redeem_code = Str::uuid();

            if (isset($data_request['variant_id'])) {
                $item_gift_variant = $item_gift->variants()->lockForUpdate()->find($data_request['variant_id']);

                if (is_null($item_gift_variant)) {
                    return response()->json([
                        'message' => trans('error.variant_not_found_in_item_gifts'),
                        'status' => 400,
                    ], 400);
                }

                // Lock the variants row for update
                $variant_locked = true;
            }

            if ($item_gift->variants->count() > 0) {
                if (!isset($data_request['variant_id'])) {
                    return response()->json([
                        'message' => trans('error.variant_required', ['id' => $item_gift->id]),
                        'status' => 400,
                    ], 400);
                }
            }

            // Lock the item_gifts row for update
            $item_gift->lockForUpdate()->get();

            if ($item_gift->item_gift_quantity == 0) {
                $item_gift->update(['item_gift_status' => 'O']);
            }

            if ($item_gift_variant) {
                $total_point = $item_gift_variant->variant_point * $data_request['redeem_quantity'];
            } else {
                $total_point = $item_gift->item_gift_point * $data_request['redeem_quantity'];
            }

            if (!$item_gift || $item_gift->item_gift_quantity < $data_request['redeem_quantity'] || $item_gift->item_gift_status == 'O') {
                return response()->json([
                    'message' => trans('error.variant_out_of_stock', ['id' => $item_gift->id, 'variant_id' => $item_gift_variant->id]),
                    'status' => 400,
                ], 400);
            }

            $redeem = Redeem::create([
                'user_id' => auth()->user()->id,
                'redeem_code' => $redeem_code,
                'total_point' => $total_point,
                'redeem_date' => date('Y-m-d'),
            ]);

            $redeem_item_gift = new RedeemItemGift([
                'item_gift_id' => $item_gift->id,
                'variant_id' => $data_request['variant_id'] ?? null,
                'redeem_quantity' => $data_request['redeem_quantity'],
                'redeem_point' => $total_point,
            ]);

            array_push($metadata_redeem_item_gifts, $redeem_item_gift->toArray());

            $transaction_details = [
                'order_id' => $redeem->id . '-' . Str::random(5),
                'gross_amount' => $total_point
            ];
    
            $item_details = [
                [
                    'id' => $item_gift->id,
                    'price' => $item_gift->item_gift_point,
                    'quantity' => $data_request['redeem_quantity'],
                    'name' => ($item_gift->variants->count() > 0) ? mb_strimwidth($item_gift->item_gift_name . ' - ' . $item_gift_variant->variant_name, 0, 50, '..') : mb_strimwidth($item_gift->item_gift_name, 0, 50, '..'),
                ]
            ];
    
            $customer_details = [
                'first_name' => auth()->user()->name,
                'email' => auth()->user()->email
            ];
    
            $midtrans_params = [
                'transaction_details' => $transaction_details,
                'item_details' => $item_details,
                'customer_details' => $customer_details
            ];

            $redeem->snap_url = $this->get_snap_url_midtrans($midtrans_params);
            $redeem->metadata = [
                'user_id' => auth()->user()->id,
                'redeem_code' => $redeem_code,
                'redeem_item_gifts' => $metadata_redeem_item_gifts,
                'total_point' => $total_point,
                'redeem_date' => date('Y-m-d'),
            ];
            $redeem->save();

            $redeem->redeem_item_gifts()->save($redeem_item_gift);

            $item_gift->item_gift_quantity -= $data_request['redeem_quantity'];
            $item_gift->save();

            if ($variant_locked) {
                if ($item_gift_variant->variant_quantity == 0 || $item_gift_variant->variant_quantity < $data_request['redeem_quantity']) {
                    return response()->json([
                        'message' => trans('error.variant_out_of_stock', ['id' => $item_gift->id, 'variant_id' => $item_gift_variant->id]),
                        'status' => 400,
                    ], 400);
                }

                $item_gift_variant->update([
                    'variant_quantity' => $item_gift_variant->variant_quantity - $data_request['redeem_quantity'],
                ]);
            }

            $header_data = [
                'redeem_code' => $redeem->redeem_code,
                'total_price' => $total_point,
            ];

            $detail_data = [
                [
                    'price' => intval($item_gift->item_gift_point),
                    'quantity' => $data_request['redeem_quantity'],
                    'name' => ($item_gift->variants->count() > 0) ? $item_gift->item_gift_name . ' - ' . $item_gift_variant->variant_name : $item_gift->item_gift_name,
                ]
            ];

            // dispatch(new SendEmailRedeemConfirmationJob($customer_details, $header_data, $detail_data));

            DB::commit();

            return response()->json([
                'message' => trans('all.success_redeem'),
                'status' => 200,
                'error' => 0,
            ]);
        } catch (QueryException $e) {
            DB::rollback();
        }
    }

    public function redeem_multiple($locale, $data)
    {
        $data_request = Arr::only($data, [
            'item_gift_id',
            'variant_id',
            'redeem_quantity',
        ]);

        $this->item_gift_repository->validate($data_request, [
                'item_gift_id' => [
                    'required',
                ],
                'item_gift_id.*' => [
                    'required',
                    'exists:item_gifts,id',
                ],
                'variant_id' => [
                    'nullable',
                ],
                'variant_id.*' => [
                    'nullable',
                    'exists:variants,id',
                ],
                'redeem_quantity' => [
                    'required',
                ],
                'redeem_quantity.*' => [
                    'required',
                    'numeric',
                    'min:1',
                ],
            ]
        );

        DB::beginTransaction();

        try {
            $total_point = 0;
            $metadata_redeem_item_gifts = [];
            $redeem_code = Str::uuid();
            $item_details = [];

            $redeem = Redeem::create([
                'user_id' => auth()->user()->id,
                'redeem_code' => $redeem_code,
                'total_point' => $total_point,
                'redeem_date' => date('Y-m-d'),
            ]);

            foreach ($data_request['item_gift_id'] as $key => $item_gift_id) {
                $quantity = $data_request['redeem_quantity'][$key];
                $variant_id = $data_request['variant_id'][$key] ?? null;

                // Lock the item_gift row for update
                $item_gift = ItemGift::lockForUpdate()->find($item_gift_id);

                if (!$item_gift || $item_gift->item_gift_quantity < $quantity || $item_gift->item_gift_status == 'O') {
                    return response()->json([
                        'message' => trans('error.out_of_stock', ['id' => $item_gift->id]),
                        'status' => 400,
                    ], 400);
                }

                if ($item_gift->variants->count() > 0) {
                    if (!isset($variant_id)) {
                        return response()->json([
                            'message' => trans('error.variant_required', ['id' => $item_gift->id]),
                            'status' => 400,
                        ], 400);
                    }
                }

                if ($item_gift->variants->count() == 0) {
                    if (isset($variant_id)) {
                        return response()->json([
                            'message' => trans('error.variant_not_found_in_item_gifts', ['id' => $item_gift->id]),
                            'status' => 400,
                        ], 400);
                    }
                }

                $subtotal = 0;

                if ($variant_id) {
                    // Lock the variant row for update
                    $variant = $item_gift->variants()->lockForUpdate()->find($variant_id);
                    
                    if ($variant) {
                        $subtotal = $variant->variant_point * $quantity;
                        $variant->update([
                            'variant_quantity' => $variant->variant_quantity - $quantity,
                        ]);
                    }
                } else {
                    $subtotal = $item_gift->item_gift_point * $quantity;
                }

                $total_point += $subtotal;

                $redeem_item_gift = new RedeemItemGift([
                    'item_gift_id' => $item_gift->id,
                    'variant_id' => $variant_id,
                    'redeem_quantity' => $quantity,
                    'redeem_point' => $subtotal,
                ]);

                array_push($metadata_redeem_item_gifts, $redeem_item_gift->toArray());
                array_push($item_details, [
                    'id' => $item_gift->id,
                    'price' => $item_gift->item_gift_point,
                    'quantity' => $quantity,
                    'name' => ($item_gift->variants->count() > 0) ? mb_strimwidth($item_gift->item_gift_name . ' - ' . $variant->variant_name, 0, 50, '..') : mb_strimwidth($item_gift->item_gift_name, 0, 50, '..'),
                ]);
        
                $redeem->redeem_item_gifts()->save($redeem_item_gift);

                $item_gift->item_gift_quantity -= $quantity;
                $item_gift->save();
            }

            $transaction_details = [
                'order_id' => $redeem->id . '-' . Str::random(5),
                'gross_amount' => $total_point
            ];

            $customer_details = [
                'first_name' => auth()->user()->name,
                'email' => auth()->user()->email
            ];
    
            $midtrans_params = [
                'transaction_details' => $transaction_details,
                'item_details' => $item_details,
                'customer_details' => $customer_details
            ];

            $redeem->snap_url = $this->get_snap_url_midtrans($midtrans_params);
            $redeem->metadata = [
                'user_id' => auth()->user()->id,
                'redeem_code' => $redeem_code,
                'redeem_item_gifts' => $metadata_redeem_item_gifts,
                'total_point' => $total_point,
                'redeem_date' => date('Y-m-d'),
            ];
            $redeem->total_point = $total_point;
            $redeem->save();

            DB::commit();

            return response()->json([
                'message' => trans('all.success_redeem'),
                'status' => 200,
                'error' => 0
            ]);
        } catch (QueryException $e) {
            DB::rollback();
        }

    }

    public function checkout($locale, $data)
    {
        $data_request = Arr::only($data, [
            'item_gift_id',
            'variant_id',
            'redeem_quantity',
            'shipping_origin',
            'shipping_destination',
            'shipping_weight',
            'shipping_courier',
        ]);

        $this->item_gift_repository->validate($data_request, [
                'item_gift_id' => [
                    'required',
                ],
                'item_gift_id.*' => [
                    'required',
                    'exists:item_gifts,id',
                ],
                'variant_id' => [
                    'nullable',
                ],
                'variant_id.*' => [
                    'nullable',
                    'exists:variants,id',
                ],
                'redeem_quantity' => [
                    'required',
                ],
                'redeem_quantity.*' => [
                    'required',
                    'numeric',
                    'min:1',
                ],
                'shipping_origin' => [
                    'required',
                    'string',
                ],
                'shipping_destination' => [
                    'required',
                    'string',
                ],
                'shipping_weight' => [
                    'required',
                    'numeric',
                ],
                'shipping_courier' => [
                    'required',
                    'in:jne,pos,tiki',
                ],
            ]
        );

        DB::beginTransaction();

        try {
            $total_point = 0;
            $metadata_redeem_item_gifts = [];
            $redeem_code = Str::uuid();
            $item_details = [];

            $redeem = Redeem::create([
                'user_id' => auth()->user()->id,
                'redeem_code' => $redeem_code,
                'total_point' => $total_point,
                'redeem_date' => date('Y-m-d'),
            ]);

            foreach ($data_request['item_gift_id'] as $key => $item_gift_id) {
                $quantity = $data_request['redeem_quantity'][$key];
                $variant_id = $data_request['variant_id'][$key] ?? null;

                // Lock the item_gift row for update
                $item_gift = ItemGift::lockForUpdate()->find($item_gift_id);

                if (!$item_gift || $item_gift->item_gift_quantity < $quantity || $item_gift->item_gift_status == 'O') {
                    return response()->json([
                        'message' => trans('error.out_of_stock', ['id' => $item_gift->id]),
                        'status' => 400,
                    ], 400);
                }

                if ($item_gift->variants->count() > 0) {
                    if (!isset($variant_id)) {
                        return response()->json([
                            'message' => trans('error.variant_required', ['id' => $item_gift->id]),
                            'status' => 400,
                        ], 400);
                    }
                }

                if ($item_gift->variants->count() == 0) {
                    if (isset($variant_id)) {
                        return response()->json([
                            'message' => trans('error.variant_not_found_in_item_gifts', ['id' => $item_gift->id]),
                            'status' => 400,
                        ], 400);
                    }
                }

                $subtotal = 0;

                if ($variant_id) {
                    // Lock the variant row for update
                    $variant = $item_gift->variants()->lockForUpdate()->find($variant_id);
                    
                    if ($variant) {
                        $subtotal = $variant->variant_point * $quantity;
                        $variant->update([
                            'variant_quantity' => $variant->variant_quantity - $quantity,
                        ]);
                    }
                } else {
                    $subtotal = $item_gift->item_gift_point * $quantity;
                }

                $total_point += $subtotal;

                $redeem_item_gift = new RedeemItemGift([
                    'item_gift_id' => $item_gift->id,
                    'variant_id' => $variant_id,
                    'redeem_quantity' => $quantity,
                    'redeem_point' => $subtotal,
                ]);

                array_push($metadata_redeem_item_gifts, $redeem_item_gift->toArray());
                array_push($item_details, [
                    'id' => $item_gift->id,
                    'price' => $item_gift->item_gift_point,
                    'quantity' => $quantity,
                    'name' => ($item_gift->variants->count() > 0) ? mb_strimwidth($item_gift->item_gift_name . ' - ' . $variant->variant_name, 0, 50, '..') : mb_strimwidth($item_gift->item_gift_name, 0, 50, '..'),
                ]);
        
                $redeem->redeem_item_gifts()->save($redeem_item_gift);

                $item_gift->item_gift_quantity -= $quantity;
                $item_gift->save();
            }

            $request = [
                'origin_city' => $data_request['shipping_origin'],
                'destination_city' => $data_request['shipping_destination'],
                'weight' => $data_request['shipping_weight'],
                'courier' => $data_request['shipping_courier'],
            ];

            $shippings = $this->rajaongkir_service->getCost($locale, $request);

            dd($shippings);

            $shipping = new Shipping([
                'redeem_id' => $redeem->id,
                'origin' => $data_request['shipping_origin'],
                'destination' => $data_request['shipping_destination'],
                'weight' => $data_request['shipping_weight'],
                'courier' => $data_request['shipping_courier'],
                'cost' => $shipping_costs['cost'],
            ]);
            $shipping->save();

            $transaction_details = [
                'order_id' => $redeem->id . '-' . Str::random(5),
                'gross_amount' => $total_point
            ];

            $customer_details = [
                'first_name' => auth()->user()->name,
                'email' => auth()->user()->email
            ];
    
            $midtrans_params = [
                'transaction_details' => $transaction_details,
                'item_details' => $item_details,
                'customer_details' => $customer_details
            ];

            $redeem->snap_url = $this->get_snap_url_midtrans($midtrans_params);
            $redeem->metadata = [
                'user_id' => auth()->user()->id,
                'redeem_code' => $redeem_code,
                'redeem_item_gifts' => $metadata_redeem_item_gifts,
                'total_point' => $total_point,
                'redeem_date' => date('Y-m-d'),
            ];
            $redeem->total_point = $total_point;
            $redeem->save();

            DB::commit();

            return response()->json([
                'message' => trans('all.success_redeem'),
                'status' => 200,
                'error' => 0
            ]);
        } catch (QueryException $e) {
            DB::rollback();
        }

    }

    public function delete($locale, $id)
    {
        $check_data = $this->repository->getSingleData($locale, $id);
        
        DB::beginTransaction();
        $redeem_item_gift = $check_data->redeem_item_gifts()->get();
        foreach ($redeem_item_gift as $value) {
            $item_gift = ItemGift::find($value->item_gift_id);
            $item_gift->update([
                'item_gift_quantity' => $item_gift->item_gift_quantity + $value->redeem_quantity,
            ]);
            if($item_gift->variants()->count() > 0) {
                $variant = $item_gift->variants()->get();
                foreach ($variant as $v) {
                    $v->update([
                        'variant_quantity' => $v->variant_quantity + $value->redeem_quantity,
                    ]);
                }
            }
        }
        $result = $check_data->delete();
        DB::commit();

        return $result;
    }

    private function get_snap_url_midtrans($params)
    {
        \Midtrans\Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        \Midtrans\Config::$isProduction = (bool) env('MIDTRANS_PRODUCTION');
        \Midtrans\Config::$is3ds = (bool) env('MIDTRANS_3DS');

        $snap_url = \Midtrans\Snap::createTransaction($params)->redirect_url;
        return $snap_url;
    }
}
