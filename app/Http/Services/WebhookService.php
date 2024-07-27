<?php

namespace App\Http\Services;

use App\Http\Models\Shipping;
use App\Http\Models\PaymentLog;
use App\Http\Models\OrderProduct;
use App\Exceptions\ApplicationException;
use App\Http\Repositories\OrderRepository;
use App\Jobs\SendEmailOrderConfirmationJob;

class WebhookService extends BaseService
{
    private $modelPaymentLog, $modelOrderProduct, $modelShipping, $orderRepository;

    public function __construct(PaymentLog $modelPaymentLog, OrderProduct $modelOrderProduct, Shipping $modelShipping, OrderRepository $orderRepository)
    {
        $this->modelPaymentLog = $modelPaymentLog;
        $this->modelOrderProduct = $modelOrderProduct;
        $this->modelShipping = $modelShipping;
        $this->orderRepository = $orderRepository;
    }

    public function midtrans_handler($locale, $data)
    {
        $signature_key = $data['signature_key'];
        $order_id = $data['order_id'];
        $status_code = $data['status_code'];
        $gross_amount = $data['gross_amount'];
        $transaction_status = $data['transaction_status'];
        $type = $data['payment_type'];
        $fraud_status = $data['fraud_status'];

        $server_key = config('services.midtrans.server_key');
        $my_signature_key = hash('sha512', $order_id.$status_code.$gross_amount.$server_key);

        if ($signature_key !== $my_signature_key) throw new ApplicationException(trans('error.invalid_signature_midtrans'));

        $real_order_id = explode('-', $order_id);
        $order = $this->orderRepository->getSingleData($locale, $real_order_id[0]);

        if ($order->status == 'shipped' && $order->status == 'success') throw new ApplicationException(trans('error.operation_not_permitted'));

        if ($transaction_status == 'capture'){
            if ($fraud_status == 'challenge'){
                $order->status = 'challenge';
            } else if ($fraud_status == 'accept'){
                $order->status = 'shipped';
            }
        } else if ($transaction_status == 'settlement'){
            $order->status = 'shipped';
        } else if ($transaction_status == 'cancel' ||
        $transaction_status == 'deny' ||
        $transaction_status == 'expire'){
            $order->status = 'failure';
        } else if ($transaction_status == 'pending'){
            $order->status = 'pending';
        }

        $payment_log_data = [
            'status' => $transaction_status,
            'raw_response' => json_encode($data),
            'order_id' => $real_order_id[0],
            'type' => $type
        ];
        $this->modelPaymentLog->create($payment_log_data);

        $order->save();

        if ($order->status == 'shipped') {
            $header_data = [
                'code' => $order->code,
                'total_price' => $order->total_point,
                'shipping_fee' => $order->shipping_fee,
                'total_amount' => $order->total_amount,
            ];

            $order_products = $this->modelOrderProduct->with(['products', 'variants'])->where('order_id', $order->id)->get();

            $detail_data = [];

            foreach ($order_products as $order_product) {
                $variant_name = '';
                $price = $order_product->products->point;

                if ($order_product->variants) {
                    $variant_name = ' - ' . $order_product->variants->name;
                    $price = $order_product->variants->point;
                }

                $detail_data[] = [
                    'price' => (int) $price,
                    'quantity' => $order_product->quantity,
                    'name' => $order_product->products->name . $variant_name,
                ];
            }

            $shipping = $this->modelShipping->where('order_id', $order->id)->first();
            if($shipping->resi == null) $shipping_status = 'on progress';
            else $shipping_status = 'on delivery';
            $shipping->update([
                'status' => $shipping_status
            ]);

            $payment = $this->modelPaymentLog->where('order_id', $order->id)->first();
            $payment->update([
                'status' => $transaction_status,
                'raw_response' => json_encode($data)
            ]);

            SendEmailOrderConfirmationJob::dispatch($order->users->email, $header_data, $detail_data);
        }

        return response()->noContent();
    }
}
