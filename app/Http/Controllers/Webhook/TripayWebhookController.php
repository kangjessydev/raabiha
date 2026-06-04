<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\Log;

class TripayWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $privateKey = env('TRIPAY_PRIVATE_KEY');
        
        $callbackSignature = $request->server('HTTP_X_CALLBACK_SIGNATURE');
        $json = $request->getContent();
        $signature = hash_hmac('sha256', $json, $privateKey);

        if ($signature !== (string) $callbackSignature) {
            Log::warning('Invalid Tripay Webhook Signature', ['ip' => $request->ip()]);
            return response()->json([
                'success' => false,
                'message' => 'Invalid signature',
            ], 403);
        }

        if ('payment_status' !== (string) $request->server('HTTP_X_CALLBACK_EVENT')) {
            return response()->json([
                'success' => false,
                'message' => 'Unrecognized event, expected payment_status',
            ], 400);
        }

        $data = json_decode($json);

        if (JSON_ERROR_NONE !== json_last_error()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid data sent by Tripay',
            ], 400);
        }

        $orderNumber = $data->merchant_ref;
        $tripayReference = $data->reference;
        $status = strtoupper((string) $data->status);

        $order = Order::where('order_number', $orderNumber)->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found',
            ], 404);
        }

        if ($order->payment_status === 'paid') {
            return response()->json([
                'success' => true,
                'message' => 'Order already paid',
            ]);
        }

        if ($status === 'PAID') {
            $order->update([
                'status' => 'paid',
                'payment_status' => 'paid',
            ]);
            Log::info('Order paid via Tripay', ['order_id' => $order->id, 'reference' => $tripayReference]);
        } elseif (in_array($status, ['EXPIRED', 'FAILED'])) {
            $order->update([
                'status' => 'cancelled',
                'payment_status' => 'failed',
            ]);
            Log::info('Order failed/expired via Tripay', ['order_id' => $order->id, 'reference' => $tripayReference]);
        }

        return response()->json(['success' => true]);
    }
}
