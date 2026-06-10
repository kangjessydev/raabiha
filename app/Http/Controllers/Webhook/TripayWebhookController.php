<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
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

        $admins = User::role('super_admin')->get();
        if ($admins->isEmpty()) {
            $admins = User::where('id', 2)->get();
        }

        if ($status === 'PAID') {
            $order->update([
                'status' => 'packed',
                'payment_status' => 'paid',
            ]);
            Log::info('Order paid via Tripay', ['order_id' => $order->id, 'reference' => $tripayReference]);

            foreach ($admins as $admin) {
                Notification::make()
                    ->icon('heroicon-o-banknotes')
                    ->iconColor('success')
                    ->title('💳 Pembayaran Diterima (Tripay)')
                    ->body("Pesanan #{$order->order_number} telah lunas via Tripay. Total: Rp " . number_format($order->grand_total, 0, ',', '.'))
                    ->sendToDatabase($admin);
            }
        } elseif (in_array($status, ['EXPIRED', 'FAILED'])) {
            $order->update([
                'status' => 'cancelled',
                'payment_status' => 'failed',
            ]);
            Log::info('Order failed/expired via Tripay', ['order_id' => $order->id, 'reference' => $tripayReference]);

            foreach ($admins as $admin) {
                Notification::make()
                    ->icon('heroicon-o-x-circle')
                    ->iconColor('danger')
                    ->title('❌ Pembayaran Gagal/Kadaluarsa (Tripay)')
                    ->body("Pesanan #{$order->order_number} gagal/kadaluarsa di Tripay.")
                    ->sendToDatabase($admin);
            }
        }

        return response()->json(['success' => true]);
    }
}
