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

class XenditWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $verificationToken = SiteSetting::where('key', 'xendit_webhook_token')->value('value') ?: env('XENDIT_WEBHOOK_TOKEN');
        if (empty($verificationToken)) {
            Log::error('Xendit Webhook Error: Verification token is not configured.');
            return response()->json([
                'success' => false,
                'message' => 'Payment gateway not configured',
            ], 500);
        }
        
        $callbackToken = (string) $request->header('x-callback-token');

        if (empty($callbackToken) || !hash_equals((string) $verificationToken, $callbackToken)) {
            Log::warning('Invalid Xendit Webhook Token', ['ip' => $request->ip()]);
            return response()->json([
                'success' => false,
                'message' => 'Invalid webhook token',
            ], 403);
        }

        $data = $request->all();
        
        if (empty($data['external_id'])) {
            return response()->json([
                'success' => false,
                'message' => 'Missing external_id',
            ], 400);
        }

        $orderNumber = $data['external_id'];
        $status = strtoupper((string) ($data['status'] ?? ''));
        $xenditInvoiceId = $data['id'] ?? null;

        // Bypass untuk simulasi "Tes dan simpan" dari dasbor Xendit
        if (str_starts_with($orderNumber, 'invoice_') || str_starts_with($orderNumber, 'demo_') || $orderNumber === 'invoice_123124123') {
            return response()->json([
                'success' => true,
                'message' => 'Test webhook received successfully',
            ]);
        }

        $admins = User::role('super_admin')->get();
        if ($admins->isEmpty()) {
            $admins = User::where('id', 2)->get();
        }

        $result = DB::transaction(function () use ($orderNumber, $xenditInvoiceId, $status, $admins) {
            $order = Order::where('order_number', $orderNumber)->lockForUpdate()->first();

            if (!$order) {
                return ['status' => 404, 'body' => ['success' => false, 'message' => 'Order not found']];
            }

            if ($status === 'PAID' || $status === 'SETTLED') {
                if ($order->payment_status === 'paid') {
                    return ['status' => 200, 'body' => ['success' => true, 'message' => 'Order already paid']];
                }

                $order->update([
                    'status' => 'packed',
                    'payment_status' => 'paid',
                ]);
                Log::info('Order paid via Xendit', ['order_id' => $order->id, 'invoice_id' => $xenditInvoiceId]);

                foreach ($admins as $admin) {
                    Notification::make()
                        ->icon('heroicon-o-banknotes')
                        ->iconColor('success')
                        ->title('💳 Pembayaran Diterima (Xendit)')
                        ->body("Pesanan #{$order->order_number} telah lunas via Xendit. Total: Rp " . number_format($order->grand_total, 0, ',', '.'))
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
                    notes: 'Pembayaran Xendit kedaluwarsa/gagal untuk pesanan #' . $order->order_number,
                    userId: null
                );

                Log::info('Order failed/expired via Xendit', ['order_id' => $order->id, 'invoice_id' => $xenditInvoiceId]);

                foreach ($admins as $admin) {
                    Notification::make()
                        ->icon('heroicon-o-x-circle')
                        ->iconColor('danger')
                        ->title('❌ Pembayaran Gagal/Kadaluarsa (Xendit)')
                        ->body("Pesanan #{$order->order_number} gagal/kadaluarsa di Xendit.")
                        ->sendToDatabase($admin);
                }

                return ['status' => 200, 'body' => ['success' => true, 'message' => 'Order cancelled and stock restored']];
            }

            return ['status' => 200, 'body' => ['success' => true, 'message' => 'Status ignored']];
        });

        return response()->json($result['body'], $result['status']);
    }
}

