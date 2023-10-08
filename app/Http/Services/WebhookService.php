<?php

namespace App\Http\Services;

use App\Http\Models\PaymentLog;
use App\Mail\RedeemConfirmation;
use App\Http\Models\RedeemItemGift;
use Illuminate\Support\Facades\Mail;
use App\Jobs\SendEmailRedeemConfirmationJob;
use App\Http\Repositories\RedeemRepository;

class WebhookService extends BaseService
{
    private $repository;
    
    public function __construct(RedeemRepository $repository)
    {
        $this->repository = $repository;
    }

    public function midtransHandler($locale, $data)
    {
        $signature_key = $data['signature_key'];
        $order_id = $data['order_id'];
        $status_code = $data['status_code'];
        $gross_amount = $data['gross_amount'];
        $transaction_status = $data['transaction_status'];
        $type = $data['payment_type'];
        $fraud_status = $data['fraud_status'];

        $server_key = env('MIDTRANS_SERVER_KEY');
        $my_signature_key = hash('sha512', $order_id.$status_code.$gross_amount.$server_key);

        if ($signature_key !== $my_signature_key) {
            return response()->json([
                'message' => trans('error.invalid_signature_midtrans'),
                'status' => 400,
            ], 400);
        }

        $real_order_id = explode('-', $order_id);
        $redeem = $this->repository->getSingleData($locale, $real_order_id[0]);

        if ($redeem->redeem_status === 'success') {
            return response()->json([
                'message' => 'Operation not permitted',
                'status' => 405,
            ], 405);   
        }

        if ($transaction_status == 'capture'){
            if ($fraud_status == 'challenge'){
                $redeem->redeem_status = 'challenge';
            } else if ($fraud_status == 'accept'){
                $redeem->redeem_status = 'success';
            }
        } else if ($transaction_status == 'settlement'){
            $redeem->redeem_status = 'success';
        } else if ($transaction_status == 'cancel' ||
          $transaction_status == 'deny' ||
          $transaction_status == 'expire'){
            $redeem->redeem_status = 'failure';
        } else if ($transaction_status == 'pending'){
            $redeem->redeem_status = 'pending';
        }

        $payment_log_data = [
            'payment_status' => $transaction_status,
            'raw_response' => json_encode($data),
            'redeem_id' => $real_order_id[0],
            'payment_type' => $type
        ];
        PaymentLog::create($payment_log_data);

        $redeem->save();

        if ($redeem->redeem_status === 'success') {
            $header_data = [
                'redeem_code' => $redeem->redeem_code,
                'total_price' => $redeem->total_point,
                'shipping_fee' => $redeem->shipping_fee,
                'total_amount' => $redeem->total_amount,
            ];

            $redeem_item_gifts = RedeemItemGift::with(['item_gifts', 'variants'])
                ->where('redeem_id', $redeem->id)
                ->get();

            $detail_data = [];

            foreach ($redeem_item_gifts as $item) {
                $variant_name = '';

                if ($item->variants) {
                    $variant_name = ' - ' . $item->variants->variant_name;
                }

                $detail_data[] = [
                    'price' => intval($item->item_gifts->item_gift_point),
                    'quantity' => $item->redeem_quantity,
                    'name' => $item->item_gifts->item_gift_name . $variant_name,
                ];
            }

            SendEmailRedeemConfirmationJob::dispatch($redeem->users->email, $header_data, $detail_data);
        }

        return response()->json([
            'message' => 'OK',
            'status' => 200,
            'error' => 0,
        ]);
    }
}