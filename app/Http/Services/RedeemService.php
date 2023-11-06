<?php

namespace App\Http\Services;

use App\Http\Models\Cart;
use App\Http\Models\Redeem;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Http\Models\Variant;
use App\Http\Models\ItemGift;
use App\Http\Models\Shipping;
use Illuminate\Support\Facades\DB;
use App\Http\Models\RedeemItemGift;
use App\Http\Services\RedeemService;
use Illuminate\Database\QueryException;
use App\Events\RealTimeNotificationEvent;
use App\Http\Repositories\RedeemRepository;
use App\Http\Repositories\ItemGiftRepository;

class RedeemService extends BaseService
{
    private $model, $repository, $item_gift_repository, $origin;
    
    public function __construct(Redeem $model, RedeemRepository $repository, ItemGiftRepository $item_gift_repository)
    {
        $this->model = $model;
        $this->repository = $repository;
        $this->item_gift_repository = $item_gift_repository;
        $this->origin = 133;
    }

    public function getIndexData($locale, $data)
    {
        $search = [
            'redeem_code' => 'redeem_code',
            'user_id' => 'user_id',
            'total_point' => 'total_point',
            'note' => 'note',
        ];

        $search_column = [
            'id' => 'id',
            'redeem_code' => 'redeem_code',
            'user_id' => 'user_id',
            'total_point' => 'total_point',
            'note' => 'note',
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

    public function checkout($locale, $data)
    {
        $data_request = $data;

        $this->repository->validate($data_request, [
                'redeem_item_gifts_details' => [
                    'required',
                ],
                'redeem_item_gifts_details.*.item_gift_id' => [
                    'required',
                    'exists:item_gifts,id',
                ],
                'redeem_item_gifts_details.*.variant_id' => [
                    'nullable',
                    'exists:variants,id',
                ],
                'redeem_item_gifts_details.*.redeem_quantity' => [
                    'required',
                    'numeric',
                    'min:1',
                ],
                'shipping_details' => [
                    'required',
                ],
                'shipping_details.shipping_destination' => [
                    'required',
                    'string',
                ],
                'shipping_details.shipping_weight' => [
                    'required',
                    'numeric',
                ],
                'shipping_details.shipping_courier' => [
                    'required',
                    'in:jne,pos,tiki',
                ],
                'shipping_details.shipping_cost' => [
                    'required',
                    'numeric',
                ],
                'address_details' => [
                    'required',
                ],
                'address_details.id' => [
                    'required',
                ],
                'address_details.person_name' => [
                    'required',
                    'string',
                ],
                'address_details.person_phone' => [
                    'required',
                ],
            ]
        );

        try {
            DB::beginTransaction();
        
            // Initialize variables
            $total_point = 0;
            $metadata_redeem_item_gifts = [];
            $redeem_code = Str::uuid();
            $item_details = [];
            $user = auth()->user();
            $redeem_details = $data_request['redeem_details'];
            $redeem_item_gifts_details = $data_request['redeem_item_gifts_details'];
            $address_details = $data_request['address_details'];
            $shipping_details = $data_request['shipping_details'];
            $shipping_cost = (int) $shipping_details['shipping_cost'];
        
            // Create Redeem entry
            $redeem = Redeem::create([
                'user_id' => $user->id,
                'address_id' => (int) $address_details['id'],
                'redeem_code' => $redeem_code,
                'total_point' => $total_point,
                'shipping_fee' => $shipping_cost,
                'total_amount' => $total_point + $shipping_cost,
                'redeem_date' => date('Y-m-d'),
                'note' => $redeem_details['note'],
            ]);
        
            // Process Redeem Item Gifts
            foreach ($redeem_item_gifts_details as $redeem_item_gifts) {
                $redeem_quantity = $redeem_item_gifts['redeem_quantity'];
                $variant_id = $redeem_item_gifts['variant_id'] ?? null;
        
                $item_gift = ItemGift::lockForUpdate()->find($redeem_item_gifts['item_gift_id']);
        
                // Check variant if required
                if ($item_gift->variants->count() > 0 && !isset($variant_id)) {
                    return response()->json([
                        'message' => trans('error.variant_required', ['id' => $item_gift->id]),
                        'status' => 400,
                    ], 400);
                } else if ($item_gift->variants->count() == 0 && isset($variant_id)) {
                    return response()->json([
                        'message' => trans('error.variant_not_found_in_item_gifts', ['id' => $item_gift->id]),
                        'status' => 400,
                    ], 400);
                }

                // Check item availability
                if (!$item_gift || $item_gift->item_gift_quantity < $redeem_quantity || $item_gift->item_gift_status == 'O') {
                    return response()->json([
                        'message' => trans('error.out_of_stock'),
                        'status' => 400,
                    ], 400);
                }
        
                // Process the chosen variant (if any)
                $subtotal = 0;
                if ($variant_id) {
                    $variant = $item_gift->variants()->lockForUpdate()->find($variant_id);

                    if ($variant->variant_quantity == 0 || $redeem_quantity > $variant->variant_quantity) {
                        return response()->json([
                            'message' => trans('error.out_of_stock'),
                            'status' => 400,
                        ], 400);
                    }
        
                    if ($variant) {
                        $subtotal = $variant->variant_point * $redeem_quantity;
                        $variant->update([
                            'variant_quantity' => $variant->variant_quantity - $redeem_quantity,
                        ]);
                    }
                } else {
                    $subtotal = $item_gift->item_gift_point * $redeem_quantity;
                }
        
                $total_point += $subtotal;
        
                // Create RedeemItemGift entry
                $redeem_item_gift = new RedeemItemGift([
                    'item_gift_id' => $item_gift->id,
                    'variant_id' => $variant_id,
                    'redeem_quantity' => (int) $redeem_quantity,
                    'redeem_point' => $subtotal,
                ]);
        
                $metadata_redeem_item_gifts[] = $redeem_item_gift->toArray();
                $item_details[] = [
                    'id' => $item_gift->id,
                    'price' => ($variant_id) ? $variant->variant_point : $item_gift->item_gift_point,
                    'quantity' => $redeem_quantity,
                    'name' => ($item_gift->variants->count() > 0) ? mb_strimwidth($item_gift->item_gift_name . ' - ' . $variant->variant_name, 0, 50, '..') : mb_strimwidth($item_gift->item_gift_name, 0, 50, '..'),
                ];

                $redeem->redeem_item_gifts()->save($redeem_item_gift);

                $item_gift->item_gift_quantity -= $redeem_quantity;
                $item_gift->save();
            }
        
            // Create shipping and transaction details
            $transaction_details = [
                'order_id' => $redeem->id . '-' . Str::random(5),
                'gross_amount' => $total_point + $shipping_cost,
            ];
        
            $customer_details = [
                'first_name' => $address_details['person_name'],
                'phone' => $address_details['person_phone'],
                'email' => $user->email,
            ];
        
            $item_details[] = [
                'price' => $shipping_cost,
                'quantity' => 1,
                'name' => '(+) Shipping Fee',
            ];
        
            $midtrans_params = [
                'transaction_details' => $transaction_details,
                'item_details' => $item_details,
                'customer_details' => $customer_details,
            ];
        
            // Update Redeem and related data
            $redeem->snap_url = $this->get_snap_url_midtrans($midtrans_params);
            $redeem->metadata = [
                'user_id' => $user->id,
                'address_id' => (int) $address_details['id'],
                'redeem_code' => $redeem_code,
                'redeem_item_gifts' => $metadata_redeem_item_gifts,
                'total_point' => $total_point,
                'shipping_fee' => $shipping_cost,
                'total_amount' => $total_point + $shipping_cost,
                'redeem_date' => date('Y-m-d'),
                'note' => $redeem_details['note'],
            ];
            $redeem->total_point = $total_point;
            $redeem->shipping_fee = $shipping_cost;
            $redeem->total_amount = $total_point + $shipping_cost;
            $redeem->save();
        
            // Create shipping record
            $shipping = new Shipping([
                'redeem_id' => $redeem->id,
                'origin' => $this->origin,
                'destination' => $shipping_details['shipping_destination'],
                'weight' => $shipping_details['shipping_weight'],
                'courier' => $shipping_details['shipping_courier'],
                'service' => $shipping_details['shipping_service'],
                'description' => $shipping_details['shipping_description'],
                'cost' => $shipping_cost,
                'etd' => $shipping_details['shipping_etd'],
            ]);
            $shipping->save();
        
            // Create and broadcast a notification
            $data_notification = [
                'user_id' => $user->id,
                'title' => trans('all.notification_transaction_title'),
                'text' => trans('all.notification_transaction_text'),
                'type' => 0,
                'status_read' => 0,
            ];
            $notification = store_notification($data_notification);
            broadcast(new RealTimeNotificationEvent($notification, $user->id));
        
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

    public function delete($locale, $id)
    {
        $check_data = $this->repository->getSingleData($locale, $id);
        
        DB::beginTransaction();
        if($check_data->redeem_status != 'success'){
            $redeem_item_gifts = $check_data->redeem_item_gifts()->get();
            foreach ($redeem_item_gifts as $redeem_item) {
                $item_gift = ItemGift::find($redeem_item->item_gift_id);
                $variant = Variant::find($redeem_item->variant_id);
                if ($item_gift) {
                    $item_gift->item_gift_quantity += $redeem_item->redeem_quantity;
                    $item_gift->save();
                }
                if ($variant) {
                    $variant->variant_quantity += $redeem_item->redeem_quantity;
                    $variant->save();
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
