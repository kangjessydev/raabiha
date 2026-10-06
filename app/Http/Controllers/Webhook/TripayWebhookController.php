<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\SiteSetting;
use App\Models\StockLog;
use App\Models\User;
use App\Models\Voucher;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TripayWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $privateKey = SiteSetting::where('key', 'tripay_private_key')->value('value') ?: env('TRIPAY_PRIVATE_KEY');
        if (empty($privateKey)) {
            Log::error('Tripay Webhook Error: Private key is not configured.');
            return response()->json([
                'success' => false,
                'message' => 'Payment gateway not configured',
            ], 500);
        }
        
        $callbackSignature = (string) $request->server('HTTP_X_CALLBACK_SIGNATURE');
        $json = $request->getContent();
        $signature = hash_hmac('sha256', $json, $privateKey);

        if (empty($callbackSignature) || !hash_equals($signature, $callbackSignature)) {
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

        if (JSON_ERROR_NONE !== json_last_error() || empty($data->merchant_ref)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid data sent by Tripay',
            ], 400);
        }

        $orderNumber = $data->merchant_ref;
        $tripayReference = $data->reference ?? null;
        $status = strtoupper((string) ($data->status ?? ''));

        $admins = User::role('super_admin')->get();
        if ($admins->isEmpty()) {
            $admins = User::where('id', 2)->get();
        }

        $result = DB::transaction(function () use ($orderNumber, $tripayReference, $status, $admins) {
            $order = Order::where('order_number', $orderNumber)->lockForUpdate()->first();

            if (!$order) {
                return ['status' => 404, 'body' => ['success' => false, 'message' => 'Order not found']];
            }

            if ($status === 'PAID') {
                if ($order->payment_status === 'paid') {
                    return ['status' => 200, 'body' => ['success' => true, 'message' => 'Order already paid']];
                }

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

                return ['status' => 200, 'body' => ['success' => true, 'message' => 'Payment recorded']];
            } elseif (in_array($status, ['EXPIRED', 'FAILED'])) {
                if ($order->status === 'cancelled') {
                    return ['status' => 200, 'body' => ['success' => true, 'message' => 'Order already cancelled']];
                }

                $order->update([
                    'status' => 'cancelled',
                    'payment_status' => 'failed',
                ]);

                // Pulihkan stok pesanan dan kuota voucher via centralized service
                app(\App\Services\OrderStockService::class)->restoreStock(
                    $order,
                    reason: 'Expired/Failed Order',
                    notes: 'Pengembalian stok pembayaran Tripay kedaluwarsa/gagal pesanan #' . $order->order_number,
                    userId: null
                );

                Log::info('Order failed/expired via Tripay', ['order_id' => $order->id, 'reference' => $tripayReference]);

                foreach ($admins as $admin) {
                    Notification::make()
                        ->icon('heroicon-o-x-circle')
                        ->iconColor('danger')
                        ->title('❌ Pembayaran Gagal/Kadaluarsa (Tripay)')
                        ->body("Pesanan #{$order->order_number} gagal/kadaluarsa di Tripay.")
                        ->sendToDatabase($admin);
                }

                return ['status' => 200, 'body' => ['success' => true, 'message' => 'Order cancelled and stock restored']];
            }

            return ['status' => 200, 'body' => ['success' => true, 'message' => 'Status ignored']];
        });

        return response()->json($result['body'], $result['status']);
    }
}

