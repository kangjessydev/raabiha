<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockLog;
use App\Models\Voucher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderStockService
{
    /**
     * Restore stock and vouchers for a cancelled/failed order atomically and idempotently.
     */
    public function restoreStock(Order $order, string $reason = 'order_cancelled', ?string $notes = null, ?int $userId = null): bool
    {
        return DB::transaction(function () use ($order, $reason, $notes, $userId) {
            // Lock the order row to prevent concurrent restoration
            $lockedOrder = Order::where('id', $order->id)->lockForUpdate()->first();

            if (!$lockedOrder) {
                return false;
            }

            // IDEMPOTENCY GUARD: Jika stok sudah pernah dipulihkan, abaikan
            if ($lockedOrder->is_stock_restored) {
                Log::info("OrderStockService: Stock already restored for Order #{$lockedOrder->order_number}");
                return false;
            }

            $orderNotes = $notes ?: "Restorasi stok untuk pesanan #{$lockedOrder->order_number}";

            // 1. Pulihkan stok untuk setiap item di pesanan
            foreach ($lockedOrder->items as $item) {
                if ($item->product_variant_id) {
                    $variant = ProductVariant::where('id', $item->product_variant_id)->lockForUpdate()->first();
                    if ($variant) {
                        $before = $variant->stock;
                        $variant->increment('stock', $item->quantity);
                        $after = $before + $item->quantity;

                        StockLog::create([
                            'product_id'         => $item->product_id,
                            'product_variant_id' => $item->product_variant_id,
                            'type'               => 'in',
                            'quantity_before'    => $before,
                            'quantity_change'    => $item->quantity,
                            'quantity_after'     => $after,
                            'reason'             => $reason,
                            'notes'              => $orderNotes,
                            'user_id'            => $userId ?: auth()->id(),
                        ]);
                    }
                } else {
                    $product = Product::where('id', $item->product_id)->lockForUpdate()->first();
                    if ($product) {
                        $before = $product->stock;
                        $product->increment('stock', $item->quantity);
                        $after = $before + $item->quantity;

                        StockLog::create([
                            'product_id'      => $item->product_id,
                            'type'            => 'in',
                            'quantity_before' => $before,
                            'quantity_change' => $item->quantity,
                            'quantity_after'  => $after,
                            'reason'          => $reason,
                            'notes'           => $orderNotes,
                            'user_id'         => $userId ?: auth()->id(),
                        ]);
                    }
                }
            }

            // 2. Pulihkan kuota voucher (jika ada yang digunakan)
            if (!empty($lockedOrder->applied_voucher_ids) && is_array($lockedOrder->applied_voucher_ids)) {
                foreach ($lockedOrder->applied_voucher_ids as $vId) {
                    Voucher::where('id', $vId)->where('used_count', '>', 0)->decrement('used_count');
                }
            } elseif ($lockedOrder->voucher_id) {
                Voucher::where('id', $lockedOrder->voucher_id)->where('used_count', '>', 0)->decrement('used_count');
            }

            // 3. Tandai pesanan bahwa stoknya telah berhasil dipulihkan
            $lockedOrder->update([
                'is_stock_restored' => true,
                'stock_restored_at' => now(),
            ]);

            // Sync model instance in memory
            $order->is_stock_restored = true;
            $order->stock_restored_at = $lockedOrder->stock_restored_at;

            return true;
        });
    }
}
