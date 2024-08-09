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
use App\Exceptions\ValidationException;
use App\Exceptions\SystemException;
use App\Exceptions\ApplicationException;
use App\Events\RealTimeNotificationEvent;
use App\Http\Repositories\CityRepository;
use App\Http\Repositories\OrderRepository;
use App\Http\Repositories\AddressRepository;
use App\Http\Repositories\ProductRepository;

class OrderService extends BaseService
{
    private $model, $modelProduct, $modelVariant, $modelCart, $modelNotification, $modelShipping, $repository, $productRepository, $cityRepository, $addressRepository;

    public function __construct(Order $model, Product $modelProduct, Variant $modelVariant, Cart $modelCart, Notification $modelNotification, Shipping $modelShipping, OrderRepository $repository, ProductRepository $productRepository, CityRepository $cityRepository, AddressRepository $addressRepository)
    {
        $this->model = $model;
        $this->modelProduct = $modelProduct;
        $this->modelVariant = $modelVariant;
        $this->modelCart = $modelCart;
        $this->modelNotification = $modelNotification;
        $this->modelShipping = $modelShipping;
        $this->repository = $repository;
        $this->productRepository = $productRepository;
        $this->cityRepository = $cityRepository;
        $this->addressRepository = $addressRepository;
    }

    public function index($locale, $data)
    {
        $search = [
            'code' => 'code',
            'user_id' => 'user_id',
            'total_point' => 'total_point',
            'note' => 'note',
        ];

        $searchColumn = [
            'id' => 'id',
            'code' => 'code',
            'user_id' => 'user_id',
            'total_point' => 'total_point',
            'note' => 'note',
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

    public function showByUser($locale, $data, $id)
    {
        $search = [
            'code' => 'code',
            'total_point' => 'total_point',
            'note' => 'note',
        ];

        $searchColumn = [
            'id' => 'id',
            'code' => 'code',
            'total_point' => 'total_point',
            'note' => 'note',
        ];

        $sortableAndSearchableColumn = [
            'search'        => $search,
            'search_column' => $searchColumn,
            'sort_column'   => array_merge($search, $searchColumn),
        ];

        return $this->repository->getDataByUser($locale, $sortableAndSearchableColumn, $id);
    }

    public function checkout($locale, $data)
    {
        $request = $data;

        $this->repository->validate($request, [
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
        ]);

        DB::beginTransaction();

        try {
            $user = auth()->user();
            $orderCode = (string) Str::uuid();
            $itemDetails = [];
            $metadataOrderProducts = [];
            $orderDetails = $request['order_details'];
            $orderProductsDetails = $request['order_products_details'];
            $addressDetails = $request['address_details'];
            $shippingDetails = $request['shipping_details'];
            $cost = (int) $shippingDetails['cost'];
            $totalPoint = 0;

            $address = $this->addressRepository->getSingleData($locale, $addressDetails['id']);
            $city = $this->cityRepository->getSingleData($locale, $address->city_id);

            $order = $this->model->create([
                'user_id' => $user->id,
                'address_id' => (int) $address->id,
                'code' => $orderCode,
                'total_point' => $totalPoint,
                'shipping_fee' => $cost,
                'total_amount' => $totalPoint + $cost,
                'date' => date('Y-m-d'),
                'note' => $orderDetails['note'],
            ]);

            foreach ($orderProductsDetails as $orderProducts) {
                $quantity = $orderProducts['quantity'];
                $variantId = ($orderProducts['variant_id'] == '') ? null : $orderProducts['variant_id'];

                $product = $this->modelProduct->lockForUpdate()->find($orderProducts['product_id']);

                if ($product->variants->count() > 0 && !isset($variantId)) {
                    throw new ValidationException(trans('error.variant_required', ['product_name' => $product->name]));
                } else if ($product->variants->count() == 0 && isset($variantId)) {
                    throw new ValidationException(trans('error.variant_not_found_in_products', ['product_name' => $product->name]));
                }

                if (!$product || $product->quantity < $quantity || $product->status == 'O') throw new ApplicationException(trans('error.out_of_stock'));

                $subtotal = 0;
                if (!is_null($variantId)) {
                    $variant = $product->variants()->lockForUpdate()->find($variantId);
                    $variantName = $this->modelVariant->find($variantId)->name;

                    if (is_null($variant)) throw new ValidationException(trans('error.variant_not_available_in_products', ['product_name' => $product->name, 'variant_name' => $variantName]));

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

                $totalPoint += $subtotal;

                $itemDetails[] = [
                    'id' => $product->id,
                    'price' => ($variantId) ? $variant->point : $product->point,
                    'quantity' => $quantity,
                    'name' => ($product->variants->count() > 0) ? mb_strimwidth($product->name . ' - ' . $variant->name, 0, 50, '..') : mb_strimwidth($product->name, 0, 50, '..'),
                    'brand' => ($product->brands) ? $product->brands->brand_name : null,
                    'category' => ($product->categories) ? $product->categories->name : null,
                    'merchant_name' => config('app.name'),
                ];

                $orderProduct = [
                    'product_id' => (int) $product->id,
                    'variant_id' => (int) $variantId == 0 ? null : (int) $variantId,
                    'quantity' => (int) $quantity,
                    'point' => $subtotal,
                ];

                $metadataOrderProducts[] = $orderProduct;

                $order->order_products()->create($orderProduct);

                $product->quantity -= $quantity;
                $product->save();
            }

            $transactionDetails = [
                'order_id' => $order->id . '-' . Str::random(5),
                'gross_amount' => $totalPoint + $cost,
            ];

            $customer_details = [
                'first_name' => $user->profile->name,
                'email' => $user->email,
                'phone' => $user->profile->phone_number,
                "shipping_address" => [
                    "first_name" => $address->person_name,
                    "phone" => $address->person_phone,
                    "address" => $address->street,
                    "city" => $city->name,
                    "postal_code" => $address->postal_code,
                    "country_code" => "IDN"
                ]
            ];

            $itemDetails[] = [
                'price' => $cost,
                'quantity' => 1,
                'name' => '(+) Shipping Fee',
            ];

            $midtransParams = [
                'transaction_details' => $transactionDetails,
                'item_details' => $itemDetails,
                'customer_details' => $customer_details
            ];

            $midtransData = $this->createTransactionMidtrans($midtransParams);

	        $order->snap_token = $midtransData->token;
            $order->snap_url = $midtransData->redirect_url;
            $order->metadata = [
                'user_id' => $user->id,
                'address_id' => (int) $address->id,
                'code' => $orderCode,
                'order_products' => $metadataOrderProducts,
                'total_point' => $totalPoint,
                'shipping_fee' => $cost,
                'total_amount' => $totalPoint + $cost,
                'date' => date('Y-m-d'),
                'note' => $orderDetails['note'],
            ];
            $order->total_point = $totalPoint;
            $order->shipping_fee = $cost;
            $order->total_amount = $totalPoint + $cost;
            $order->save();

            $shipping = $this->modelShipping->create([
                'order_id' => $order->id,
                'origin' => config('setting.shipping.origin_id'),
                'destination' => $shippingDetails['destination'],
                'weight' => $shippingDetails['weight'],
                'courier' => $shippingDetails['courier'],
                'service' => $shippingDetails['service'],
                'description' => $shippingDetails['description'],
                'cost' => $cost,
                'etd' => $shippingDetails['etd'],
                'status' => 'on progress',
            ]);

            if($order->metadata != null){
                $metadata = $order->metadata;
                $metadataOrderProducts = $metadata['order_products'];
                foreach ($metadataOrderProducts as $product) {
                    $user_id = (int) $order->user_id;
                    $product_id = (int) $product['product_id'];
                    $variantId = ($product['variant_id'] == null) ? '' : (int) $product['variant_id'];
                    $quantity = (int) $product['quantity'];

                    $carts = $this->modelCart->where('user_id', '=', $user_id)
                        ->where('product_id', '=', $product_id)
                        ->where('variant_id', '=', $variantId)
                        ->where('quantity', '=', $quantity)
                        ->first();

                    if(!is_null($carts)) $carts->delete();
                }
            }

            $inputNotification = [
                'user_id' => $user->id,
                'title' => trans('all.notification_transaction_title'),
                'text' => trans('all.notification_transaction_text'),
                'type' => 0,
                'status_read' => 0,
            ];

            $allNotifications = storeNotification($inputNotification);

            $dataNotification['data'] = $allNotifications->toArray();
            $dataNotification['summary'] = [
                'total_data' => $this->modelNotification->where('user_id', $user->id)->count(),
                'total_read' => $this->modelNotification->Read()->where('user_id', $user->id)->count(),
                'total_unread' => $this->modelNotification->Unread()->where('user_id', $user->id)->count()
            ];

            broadcast(new RealTimeNotificationEvent($dataNotification, $user->id));

            $responseData = [
                'snap_token' => $order->snap_token,
                'snap_url' => $order->snap_url
            ];

            DB::commit();

            return response()->api(trans('all.success_order'), $responseData);
        } catch (\Exception $e) {
            DB::rollback();

            throw new SystemException(json_encode([$e->getMessage()]));
        }
    }

    public function cancel($locale, $id, $data)
    {
        $checkData = $this->repository->getSingleData($locale, $id);

        $data = array_merge([
            'status' => $checkData->status,
        ], $data);

        $request = Arr::only($data, [
            'status',
        ]);

        $this->repository->validate($request, [
                'status' => [
                    'required',
                    'in:cancelled'
                ],
            ]
        );

        DB::beginTransaction();

        if($checkData->status != 'shipped' && $checkData->status != 'success'){
            $orderProducts = $checkData->order_products()->get();
            foreach ($orderProducts as $orderProduct) {
                $product = $this->modelProduct->find($orderProduct->product_id);
                $variant = $this->modelVariant->find($orderProduct->variant_id);
                if ($product) {
                    $product->quantity += $orderProduct->quantity;
                    $product->save();
                }
                if ($variant) {
                    $variant->quantity += $orderProduct->quantity;
                    $variant->save();
                }
            }

            $shipping = $this->modelShipping->where('order_id', $id)->first();
            $shipping->update(['status' => 'cancelled']);

            $checkData->update($request);
            $message = trans('all.success_cancel_order');
        } else {
            $message = trans('error.failed_cancel_order');
        }

        DB::commit();

        return response()->api($message);
    }

    public function receive($locale, $id, $data)
    {
        $checkData = $this->repository->getSingleData($locale, $id);

        $data = array_merge([
            'status' => $checkData->status,
        ], $data);

        $request = Arr::only($data, [
            'status',
        ]);

        $this->repository->validate($request, [
            'status' => [
                'required',
                'in:received'
            ],
        ]);

        DB::beginTransaction();

        if($checkData->status == 'shipped' && $checkData->shippings->resi != null){
            $shipping = $this->modelShipping->where('order_id', $id)->first();
            $shipping->update(['status' => 'delivered']);

            $request['status'] = 'success';
            $checkData->update($request);
        }

        DB::commit();

        return response()->api(trans('all.success_receive_order'));
    }

    public function delete($locale, $id)
    {
        $checkData = $this->repository->getSingleData($locale, $id);

        DB::beginTransaction();

        if($checkData->status != 'cancelled' && $checkData->status != 'shipped' && $checkData->status != 'success'){
            $orderProducts = $checkData->order_products()->get();
            foreach ($orderProducts as $orderProduct) {
                $product = $this->modelProduct->find($orderProduct->product_id);
                $variant = $this->modelVariant->find($orderProduct->variant_id);
                if ($product) {
                    $product->quantity += $orderProduct->quantity;
                    $product->save();
                }
                if ($variant) {
                    $variant->quantity += $orderProduct->quantity;
                    $variant->save();
                }
            }
        }

        $shipping = $this->modelShipping->where('order_id', $id)->first();
        $shipping->update(['resi' => null]);

        $result = $checkData->update(['deleted_at' => now()->format('Y-m-d H:i:s')]);

        DB::commit();

        return $result;
    }

    private function createTransactionMidtrans($params)
    {
        \Midtrans\Config::$serverKey = config('services.midtrans.server_key');
        \Midtrans\Config::$isProduction = (bool) config('services.midtrans.production');
        \Midtrans\Config::$is3ds = (bool) config('services.midtrans.3ds');

        $result = \Midtrans\Snap::createTransaction($params);

        return $result;
    }
}
