<?php

namespace App\Http\Services;

use App\Http\Models\Redeem;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Http\Models\PaymentLog;
use App\Http\Repositories\RedeemRepository;

class WebhookService extends BaseService
{
    private $repository;
    
    public function __construct(RedeemRepository $repository)
    {
        $this->repository = $repository;
    }

    public function midtransHandler($locale, $request)
    {
        $data = $request;

        $signatureKey = $data['signature_key'];

        $orderId = $data['order_id'];
        $statusCode = $data['status_code'];
        $grossAmount = $data['gross_amount'];
        $serverKey = env('MIDTRANS_SERVER_KEY');

        $mySignatureKey = hash('sha512', $orderId.$statusCode.$grossAmount.$serverKey);

        $transactionStatus = $data['transaction_status'];
        $type = $data['payment_type'];
        $fraudStatus = $data['fraud_status'];

        if ($signatureKey !== $mySignatureKey) {
            return response()->json([
                'message' => trans('error.invalid_signature_midtrans'),
                'status' => 400,
            ], 400);
        }

        $realOrderId = explode('-', $orderId);
        // $redeem = Redeem::find($realOrderId[0]);
        $redeem = $this->repository->getSingleData($locale, $realOrderId[0]);
        // if (!$redeem) {
        //     return response()->json([
        //         'status' => 'error',
        //         'message' => 'order id not found'
        //     ], 404);
        // }

        if ($redeem->redeem_status === 'success') {
            return response()->json([
                'message' => 'Operation not permitted',
                'status' => 405,
            ], 405);
        }

        if ($transactionStatus == 'capture'){
            if ($fraudStatus == 'challenge'){
                $redeem->redeem_status = 'challenge';
            } else if ($fraudStatus == 'accept'){
                $redeem->redeem_status = 'success';
            }
        } else if ($transactionStatus == 'settlement'){
            $redeem->redeem_status = 'success';
        } else if ($transactionStatus == 'cancel' ||
          $transactionStatus == 'deny' ||
          $transactionStatus == 'expire'){
            $redeem->redeem_status = 'failure';
        } else if ($transactionStatus == 'pending'){
            $redeem->redeem_status = 'pending';
        }

        $logData = [
            'payment_status' => $transactionStatus,
            'raw_response' => json_encode($data),
            'redeem_id' => $realOrderId[0],
            'payment_type' => $type
        ];

        PaymentLog::create($logData);
        $redeem->save();

        if ($redeem->redeem_status === 'success') {
            // SEND EMAIL NOTIFICATION
        }

        return response()->json([
            'message' => 'OK',
            'status' => 200,
            'error' => 0,
        ]);;
    }
}
