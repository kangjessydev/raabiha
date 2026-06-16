<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class XenditWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $verificationToken = \App\Models\SiteSetting::where('key', 'xendit_webhook_token')->value('value') ?: env('XENDIT_WEBHOOK_TOKEN');
        
        $callbackToken = $request->header('x-callback-token');

        if ($verificationToken !== $callbackToken) {
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
        $status = strtoupper($data['status'] ?? '');
        $xenditInvoiceId = $data['id'] ?? null;

        // Bypass untuk simulasi "Tes dan simpan" dari dasbor Xendit
        if (str_starts_with($orderNumber, 'invoice_') || str_starts_with($orderNumber, 'demo_') || $orderNumber === 'invoice_123124123') {
            return response()->json([
                'success' => true,
                'message' => 'Test webhook received successfully',
            ]);
        }

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

        if ($status === 'PAID' || $status === 'SETTLED') {
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
        } elseif (in_array($status, ['EXPIRED', 'FAILED'])) {
            // Restore stock if cancelled
            foreach ($order->items as $item) {
                if ($item->product_variant_id) {
                    $variant = \App\Models\ProductVariant::find($item->product_variant_id);
                    if ($variant) {
                        $before = $variant->stock;
                        $variant->increment('stock', $item->quantity);
                        \App\Models\StockLog::create([
                            'product_id' => $item->product_id,
                            'product_variant_id' => $item->product_variant_id,
                            'type' => 'in',
                            'quantity_before' => $before,
                            'quantity_change' => $item->quantity,
                            'quantity_after' => $before + $item->quantity,
                            'reason' => 'Cancellation',
                            'notes' => 'Pembayaran Xendit kedaluwarsa/gagal untuk pesanan #' . $order->order_number,
                            'user_id' => null,
                        ]);
                    }
                } else {
                    $product = \App\Models\Product::find($item->product_id);
                    if ($product) {
                        $before = $product->stock;
                        $product->increment('stock', $item->quantity);
                        \App\Models\StockLog::create([
                            'product_id' => $item->product_id,
                            'type' => 'in',
                            'quantity_before' => $before,
                            'quantity_change' => $item->quantity,
                            'quantity_after' => $before + $item->quantity,
                            'reason' => 'Cancellation',
                            'notes' => 'Pembayaran Xendit kedaluwarsa/gagal untuk pesanan #' . $order->order_number,
                            'user_id' => null,
                        ]);
                    }
                }
            }

            $order->update([
                'status' => 'cancelled',
                'payment_status' => 'failed',
            ]);
            Log::info('Order failed/expired via Xendit', ['order_id' => $order->id, 'invoice_id' => $xenditInvoiceId]);

            foreach ($admins as $admin) {
                Notification::make()
                    ->icon('heroicon-o-x-circle')
                    ->iconColor('danger')
                    ->title('❌ Pembayaran Gagal/Kadaluarsa (Xendit)')
                    ->body("Pesanan #{$order->order_number} gagal/kadaluarsa di Xendit.")
                    ->sendToDatabase($admin);
            }
        }

        return response()->json(['success' => true]);
    }
}
