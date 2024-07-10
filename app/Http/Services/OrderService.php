<?php

namespace App\Http\Services;

use App\Http\Models\Cart;
use App\Http\Models\City;
use App\Http\Models\Order;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Http\Models\Product;
use App\Http\Models\Variant;
use App\Http\Models\Shipping;
use App\Http\Models\Notification;
use App\Http\Models\OrderProduct;
use Illuminate\Support\Facades\DB;
use App\Http\Services\OrderService;
use Illuminate\Database\QueryException;
use App\Exceptions\ApplicationException;
use App\Events\RealTimeNotificationEvent;
use App\Http\Repositories\CityRepository;
use App\Http\Repositories\OrderRepository;
use App\Http\Repositories\AddressRepository;
use App\Http\Repositories\ProductRepository;

class OrderService extends BaseService
{
    private $model, $repository, $product_repository, $city_repository, $address_repository;

    public function __construct(Order $model, OrderRepository $repository, ProductRepository $product_repository, CityRepository $city_repository, AddressRepository $address_repository)
    {
        $this->model = $model;
        $this->repository = $repository;
        $this->product_repository = $product_repository;
        $this->city_repository = $city_repository;
        $this->address_repository = $address_repository;
        $this->origin = config('setting.shipping.origin_id');
    }

    public function getIndexData($locale, $data)
    {
        $search = [
            'code' => 'code',
            'user_id' => 'user_id',
            'total_point' => 'total_point',
            'note' => 'note',
        ];

        $search_column = [
            'id' => 'id',
            'code' => 'code',
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

    public function getDataByUser($locale, $data, $id)
    {
        $search = [
            'code' => 'code',
            'total_point' => 'total_point',
            'note' => 'note',
        ];

        $search_column = [
            'id' => 'id',
            'code' => 'code',
            'total_point' => 'total_point',
            'note' => 'note',
        ];

        $sortable_and_searchable_column = [
            'search'        => $search,
            'search_column' => $search_column,
            'sort_column'   => array_merge($search, $search_column),
        ];

        return $this->repository->getDataByUser($locale, $sortable_and_searchable_column, $id);
    }

    public function getSingleData($locale, $id)
    {
        return $this->repository->getSingleData($locale, $id);
    }

    public function checkout($locale, $data)
    {
        $data_request = $data;

        $this->repository->validate($data_request, [
                'order_products_details' => [
                    'required',
                ],
                'order_products_details.*.product_id' => [
                    'required',
                    'exists:products,id',
                ],
                'order_products_details.*.variant_id' => [
                    'nullable',
                    'exists:variants,id',
                ],
                'order_products_details.*.quantity' => [
                    'required',
                    'numeric',
                    'min:1',
                ],
                'shipping_details' => [
                    'required',
                ],
                'shipping_details.destination' => [
                    'required',
                    'string',
                ],
                'shipping_details.weight' => [
                    'required',
                    'numeric',
                ],
                'shipping_details.courier' => [
                    'required',
                    'in:jne,pos,tiki',
                ],
                'shipping_details.cost' => [
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
            $metadata_order_products = [];
            $order_code = Str::uuid();
            $item_details = [];
            $user = auth()->user();
            $order_details = $data_request['order_details'];
            $order_products_details = $data_request['order_products_details'];
            $address_details = $data_request['address_details'];
            $shipping_details = $data_request['shipping_details'];
            $cost = (int) $shipping_details['cost'];
            $city = $this->city_repository->getSingleData($locale, $address_details['city_id']);

            $check_address = $this->address_repository->getSingleData($locale, $address_details['id']);

            // Create Order entry
            $order = Order::create([
                'user_id' => $user->id,
                'address_id' => (int) $address_details['id'],
                'code' => $order_code,
                'total_point' => $total_point,
                'shipping_fee' => $cost,
                'total_amount' => $total_point + $cost,
                'date' => date('Y-m-d'),
                'note' => $order_details['note'],
            ]);

            // Process Orders
            foreach ($order_products_details as $order_products) {
                $quantity = $order_products['quantity'];
                $variant_id = ($order_products['variant_id'] == '') ? null : $order_products['variant_id'];

                $product = Product::lockForUpdate()->find($order_products['product_id']);

                // Check variant if required
                if ($product->variants->count() > 0 && !isset($variant_id)) {
                    throw new ApplicationException(trans('error.variant_required', ['product_name' => $product->name]));
                } else if ($product->variants->count() == 0 && isset($variant_id)) {
                    throw new ApplicationException(trans('error.variant_not_found_in_products', ['product_name' => $product->name]));
                }

                // Check item availability
                if (!$product || $product->quantity < $quantity || $product->status == 'O') throw new ApplicationException(trans('error.out_of_stock'));

                // Process the chosen variant (if any)
                $subtotal = 0;
                if (!is_null($variant_id)) {
                    $variant = $product->variants()->lockForUpdate()->find($variant_id);
                    $variant_name = Variant::find($variant_id)->name;
                    if (is_null($variant)) throw new ApplicationException(trans('error.variant_not_available_in_products', ['product_name' => $product->name, 'variant_name' => $variant_name]));
                    if ($variant->quantity == 0 || $quantity > $variant->quantity) throw new ApplicationException(trans('error.out_of_stock'));
                    if ($variant) {
                        $subtotal = $variant->point * $quantity;
                        $variant->update([
                            'quantity' => $variant->quantity - $quantity,
                        ]);
                    }
                } else {
                    $subtotal = $product->point * $quantity;
                }

                $total_point += $subtotal;

                // Create OrderProduct entry
                $order_product = new OrderProduct([
                    'product_id' => (int) $product->id,
                    'variant_id' => (int) $variant_id == 0 ? null : (int) $variant_id,
                    'quantity' => (int) $quantity,
                    'point' => $subtotal,
                ]);

                $metadata_order_products[] = $order_product->toArray();
                $item_details[] = [
                    'id' => $product->id,
                    'price' => ($variant_id) ? $variant->point : $product->point,
                    'quantity' => $quantity,
                    'name' => ($product->variants->count() > 0) ? mb_strimwidth($product->name . ' - ' . $variant->name, 0, 50, '..') : mb_strimwidth($product->name, 0, 50, '..'),
                    'brand' => ($product->brands) ? $product->brands->brand_name : null,
                    'category' => ($product->categories) ? $product->categories->name : null,
                    'merchant_name' => config('app.name'),
                ];

                $order->order_products()->save($order_product);

                $product->quantity -= $quantity;
                $product->save();
            }

            // Create shipping and transaction details
            $transaction_details = [
                'order_id' => $order->id . '-' . Str::random(5),
                'gross_amount' => $total_point + $cost,
            ];

            $customer_details = [
                'first_name' => $user->profile->name,
                'email' => $user->email,
                'phone' => $user->profile->phone_number,
                "shipping_address" => [
                    "first_name" => $address_details['person_name'],
                    "phone" => $address_details['person_phone'],
                    "address" => $address_details['street'],
                    "city" => $city->name,
                    "postal_code" => $address_details['postal_code'],
                    "country_code" => "IDN"
                ]
            ];

            $item_details[] = [
                'price' => $cost,
                'quantity' => 1,
                'name' => '(+) Shipping Fee',
            ];

            $midtrans_params = [
                'transaction_details' => $transaction_details,
                'item_details' => $item_details,
                'customer_details' => $customer_details
            ];

            // Update Order and related data
            $midtrans_data = $this->create_transaction_midtrans($midtrans_params);
	        $order->snap_token = $midtrans_data->token;
            $order->snap_url = $midtrans_data->redirect_url;
            $order->metadata = [
                'user_id' => $user->id,
                'address_id' => (int) $address_details['id'],
                'code' => $order_code,
                'order_products' => $metadata_order_products,
                'total_point' => $total_point,
                'shipping_fee' => $cost,
                'total_amount' => $total_point + $cost,
                'date' => date('Y-m-d'),
                'note' => $order_details['note'],
            ];
            $order->total_point = $total_point;
            $order->shipping_fee = $cost;
            $order->total_amount = $total_point + $cost;
            $order->save();

            // Create shipping record
            $shipping = new Shipping([
                'order_id' => $order->id,
                'origin' => $this->origin,
                'destination' => $shipping_details['destination'],
                'weight' => $shipping_details['weight'],
                'courier' => $shipping_details['courier'],
                'service' => $shipping_details['service'],
                'description' => $shipping_details['description'],
                'cost' => $cost,
                'etd' => $shipping_details['etd'],
                'status' => 'on progress',
            ]);
            $shipping->save();

            //Delete product in cart
            if($order->metadata != null){
                $metadata = $order->metadata;
                $metadata_order_products = $metadata['order_products'];
                foreach ($metadata_order_products as $product) {
                    $user_id = (int) $order->user_id;
                    $product_id = (int) $product['product_id'];
                    $variant_id = ($product['variant_id'] == null) ? '' : (int) $product['variant_id'];
                    $quantity = (int) $product['quantity'];

                    $carts = Cart::where('user_id', '=', $user_id)
                        ->where('product_id', '=', $product_id)
                        ->where('variant_id', '=', $variant_id)
                        ->where('quantity', '=', $quantity)
                        ->first();

                    if(!is_null($carts)) $carts->delete();
                }
            }

            // Create and broadcast a notification
            $inputNotification = [
                'user_id' => $user->id,
                'title' => trans('all.notification_transaction_title'),
                'text' => trans('all.notification_transaction_text'),
                'type' => 0,
                'status_read' => 0,
            ];

            $allNotifications = store_notification($inputNotification);

            $dataNotification['data'] = $allNotifications->toArray();
            $dataNotification['summary'] = [
                'total_data' => Notification::where('user_id', $user->id)->count(),
                'total_read' => Notification::Read()->where('user_id', $user->id)->count(),
                'total_unread' => Notification::Unread()->where('user_id', $user->id)->count()
            ];

            broadcast(new RealTimeNotificationEvent($dataNotification, $user->id));

            $responseData = [
                'snap_token' => $order->snap_token,
                'snap_url' => $order->snap_url
            ];

            DB::commit();

            return response()->api(trans('all.success_order'), $responseData);
        } catch (QueryException $e) {
            DB::rollback();
            throw new ApplicationException(json_encode([$e->getMessage()]));
        }
    }

    public function cancel($locale, $id, $data)
    {
        $check_data = $this->repository->getSingleData($locale, $id);

        $data = array_merge([
            'status' => $check_data->status,
        ], $data);

        $data_request = Arr::only($data, [
            'status',
        ]);

        $this->repository->validate($data_request, [
                'status' => [
                    'required',
                    'in:cancelled'
                ],
            ]
        );

        DB::beginTransaction();
        if($check_data->status != 'shipped' && $check_data->status != 'success'){
            $order_products = $check_data->order_products()->get();
            foreach ($order_products as $order_product) {
                $product = Product::find($order_product->product_id);
                $variant = Variant::find($order_product->variant_id);
                if ($product) {
                    $product->quantity += $order_product->quantity;
                    $product->save();
                }
                if ($variant) {
                    $variant->quantity += $order_product->quantity;
                    $variant->save();
                }
            }
            $shippings = Shipping::where('order_id', $id)->first();
            $shippings->update(['status' => 'cancelled']);
            $check_data->update($data_request);
            $message = trans('all.success_cancel_order');
        } else {
            $message = trans('error.failed_cancel_order');
        }
        DB::commit();

        return response()->api($message);
    }

    public function receive($locale, $id, $data)
    {
        $check_data = $this->repository->getSingleData($locale, $id);

        $data = array_merge([
            'status' => $check_data->status,
        ], $data);

        $data_request = Arr::only($data, [
            'status',
        ]);

        $this->repository->validate($data_request, [
                'status' => [
                    'required',
                    'in:received'
                ],
            ]
        );

        DB::beginTransaction();
        if($check_data->status == 'shipped' && $check_data->shippings->resi != null){
            $shippings = Shipping::where('order_id', $id)->first();
            $shippings->update(['status' => 'delivered']);
            $data_request['status'] = 'success';
            $check_data->update($data_request);
        }
        DB::commit();

        return response()->api(trans('all.success_receive_order'));
    }

    public function delete($locale, $id)
    {
        $check_data = $this->repository->getSingleData($locale, $id);

        DB::beginTransaction();
        if($check_data->status != 'cancelled' && $check_data->status != 'shipped' && $check_data->status != 'success'){
            $order_products = $check_data->order_products()->get();
            foreach ($order_products as $order_product) {
                $product = Product::find($order_product->product_id);
                $variant = Variant::find($order_product->variant_id);
                if ($product) {
                    $product->quantity += $order_product->quantity;
                    $product->save();
                }
                if ($variant) {
                    $variant->quantity += $order_product->quantity;
                    $variant->save();
                }
            }
        }
        $shippings = Shipping::where('order_id', $id)->first();
        $shippings->update(['resi' => null]);
        $result = $check_data->update(['deleted_at' => now()->format('Y-m-d H:i:s')]);
        DB::commit();

        return $result;
    }

    private function create_transaction_midtrans($params)
    {
        \Midtrans\Config::$serverKey = config('services.midtrans.server_key');
        \Midtrans\Config::$isProduction = (bool) config('services.midtrans.production');
        \Midtrans\Config::$is3ds = (bool) config('services.midtrans.3ds');

        $result = \Midtrans\Snap::createTransaction($params);
        return $result;
    }
}
