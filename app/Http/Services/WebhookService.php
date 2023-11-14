<?php

namespace App\Http\Services;

use App\Http\Models\Cart;
use App\Http\Models\Shipping;
use App\Http\Models\PaymentLog;
use App\Http\Models\RedeemItemGift;
use App\Http\Repositories\RedeemRepository;
use App\Jobs\SendEmailRedeemConfirmationJob;

class WebhookService extends BaseService
{
    private $repository;
    
    public function __construct(RedeemRepository $repository)
    {
        $this->repository = $repository;
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
                'message' => trans('error.operation_not_permitted'),
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

            foreach ($redeem_item_gifts as $redeem_item) {
                $variant_name = '';
                $price = $redeem_item->item_gifts->item_gift_point;

                if ($redeem_item->variants) {
                    $variant_name = ' - ' . $redeem_item->variants->variant_name;
                    $price = $redeem_item->variants->variant_point;
                }

                $detail_data[] = [
                    'price' => intval($price),
                    'quantity' => $redeem_item->redeem_quantity,
                    'name' => $redeem_item->item_gifts->item_gift_name . $variant_name,
                ];
            }

            if($redeem->metadata != null){
                $metadata = json_decode($redeem->metadata, true);
                $metadata_redeem_item_gifts = $metadata['redeem_item_gifts'];
                foreach ($metadata_redeem_item_gifts as $item) {
                    $user_id = (int) $redeem->user_id;
                    $item_gift_id = (int) $item['item_gift_id'];
                    $variant_id = ($item['variant_id'] == null) ? '' : (int) $item['variant_id'];
                    $redeem_quantity = (int) $item['redeem_quantity'];

                    $carts = Cart::all()
                        ->where('user_id', '=', $user_id)
                        ->where('item_gift_id', '=', $item_gift_id)
                        ->where('variant_id', '=', $variant_id)
                        ->where('cart_quantity', '=', $redeem_quantity)
                        ->first();
                    
                    if(count($carts) > 0) {
                        $carts->delete();
                    }
                }
            }

            $shiipings = Shipping::where('redeem_id', $redeem->id);
            $shiipings->update([
                'status' => 'on progress'
            ]);

            $payment_logs = PaymentLog::where('redeem_id', $redeem->id);
            $payment_logs->update([
                'payment_status' => $transaction_status, 
                'raw_response' => json_encode($data)
            ]);

            SendEmailRedeemConfirmationJob::dispatch($redeem->users->email, $header_data, $detail_data);
        }

        return response()->json([
            'message' => 'OK',
            'status' => 200,
            'error' => 0,
        ]);
    }
}