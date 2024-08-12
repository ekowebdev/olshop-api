<?php

namespace App\Http\Services;

use App\Http\Models\Shipping;
use App\Http\Models\PaymentLog;
use App\Http\Models\OrderProduct;
use App\Exceptions\ApplicationException;
use App\Http\Repositories\OrderRepository;
use App\Jobs\SendEmailOrderConfirmationJob;
use App\Http\Repositories\ShippingRepository;
use App\Http\Repositories\PaymentLogRepository;
use App\Http\Repositories\OrderProductRepository;

class WebhookService extends BaseService
{
    private $modelPaymentLog, $modelOrderProduct, $modelShipping, $orderRepository, $orderProductRepository, $shippingRepository, $paymentLogRepository;

    public function __construct(PaymentLog $modelPaymentLog, OrderProduct $modelOrderProduct, Shipping $modelShipping, OrderRepository $orderRepository, OrderProductRepository $orderProductRepository, ShippingRepository $shippingRepository, PaymentLogRepository $paymentLogRepository)
    {
        $this->modelPaymentLog = $modelPaymentLog;
        $this->modelOrderProduct = $modelOrderProduct;
        $this->modelShipping = $modelShipping;
        $this->orderRepository = $orderRepository;
        $this->orderProductRepository = $orderProductRepository;
        $this->shippingRepository = $shippingRepository;
        $this->paymentLogRepository = $paymentLogRepository;
    }

    public function midtrans_handler($locale, $data)
    {
        $signatureKey = $data['signature_key'];
        $orderId = $data['order_id'];
        $statusCode = $data['status_code'];
        $grossAmount = $data['gross_amount'];
        $transactionStatus = $data['transaction_status'];
        $type = $data['payment_type'];
        $fraudStatus = $data['fraud_status'];

        $serverKey = config('services.midtrans.server_key');
        $mySignatureKey = hash('sha512', $orderId.$statusCode.$grossAmount.$serverKey);

        if ($signatureKey !== $mySignatureKey) throw new ApplicationException(trans('error.invalid_signature_midtrans'));

        $realOrderId = explode('-', $orderId);
        $order = $this->orderRepository->getSingleData($locale, $realOrderId[0]);

        if ($order->status == 'shipped' && $order->status == 'success') throw new ApplicationException(trans('error.operation_not_permitted'));

        if ($transactionStatus == 'capture'){
            if ($fraudStatus == 'challenge'){
                $order->status = 'challenge';
            } else if ($fraudStatus == 'accept'){
                $order->status = 'shipped';
            }
        } else if ($transactionStatus == 'settlement'){
            $order->status = 'shipped';
        } else if ($transactionStatus == 'cancel' ||
        $transactionStatus == 'deny' ||
        $transactionStatus == 'expire'){
            $order->status = 'failure';
        } else if ($transactionStatus == 'pending'){
            $order->status = 'pending';
        }

        $paymentLogData = [
            'status' => $transactionStatus,
            'raw_response' => json_encode($data),
            'order_id' => $realOrderId[0],
            'type' => $type
        ];
        $this->modelPaymentLog->create($paymentLogData);

        $order->save();

        if ($order->status == 'shipped') {
            $headerData = [
                'code' => $order->code,
                'total_price' => $order->total_point,
                'shipping_fee' => $order->shipping_fee,
                'total_amount' => $order->total_amount,
            ];

            // $orderProducts = $this->modelOrderProduct->with(['products', 'variants'])->where('order_id', $order->id)->get();
            $orderProducts = $this->orderProductRepository->getDataByOrderId('order_id', $order->id);

            $detailData = [];

            foreach ($orderProducts as $orderProduct) {
                $variantName = '';
                $price = $orderProduct->products->point;

                if ($orderProduct->variants) {
                    $variantName = ' - ' . $orderProduct->variants->name;
                    $price = $orderProduct->variants->point;
                }

                $detailData[] = [
                    'price' => (int) $price,
                    'quantity' => $orderProduct->quantity,
                    'name' => $orderProduct->products->name . $variantName,
                ];
            }

            // $shipping = $this->modelShipping->where('order_id', $order->id)->first();
            $shipping = $this->shippingRepository->getSingleDataByOrderId($order->id);
            if($shipping->resi == null) $shippingStatus = 'on progress';
            else $shippingStatus = 'on delivery';
            $shipping->update(['status' => $shippingStatus]);

            // $payment = $this->modelPaymentLog->where('order_id', $order->id)->first();
            $payment = $this->paymentLogRepository->getSingleDataByOrderId($order->id);
            $payment->update([
                'status' => $transactionStatus,
                'raw_response' => json_encode($data)
            ]);

            // SendEmailOrderConfirmationJob::dispatch($order->users->email, $headerData, $detailData);
        }

        return response()->noContent();
    }
}
