<?php

namespace App\Observers;

use App\Models\Cashflow;
use App\Models\Order;

class OrderObserver
{
    /**
     * Saat pesanan baru dibuat langsung dengan status paid (POS Manual).
     */
    public function created(Order $order): void
    {
        if ($order->payment_status === 'paid') {
            $this->recordCashIn($order);
        }
    }

    /**
     * Saat pesanan di-update:
     * - Jika payment_status berubah ke 'paid' → catat Cash In
     * - Jika status berubah ke 'cancelled' → buat reversal entry (jangan hapus)
     */
    public function updated(Order $order): void
    {
        if ($order->isDirty('payment_status') && $order->payment_status === 'paid') {
            $this->recordCashIn($order);
        }

        if ($order->isDirty('status') && $order->status === 'cancelled') {
            // Cek apakah ada Cash In aktif untuk pesanan ini
            $original = Cashflow::where('order_id', $order->id)
                ->where('type', 'in')
                ->where('source', 'order')
                ->where('is_reversed', false)
                ->first();

            if ($original) {
                // Tandai entri asli sebagai reversed
                $original->update([
                    'is_reversed'   => true,
                    'reversal_note' => 'Pesanan #' . $order->order_number . ' dibatalkan.',
                ]);

                // Buat reversal entry (audit trail)
                Cashflow::create([
                    'transaction_date' => now()->toDateString(),
                    'type'             => 'out',
                    'category'         => 'Order_Reversal',
                    'amount'           => $original->amount,
                    'description'      => 'Reversal: Pembatalan pesanan #' . $order->order_number,
                    'order_id'         => $order->id,
                    'source'           => 'order',
                    'is_reversed'      => false,
                ]);
            }
        }
    }

    public function deleted(Order $order): void {}
    public function restored(Order $order): void {}
    public function forceDeleted(Order $order): void {}

    /**
     * Helper: catat Cash In dari pesanan, cegah duplikat via updateOrInsert.
     */
    private function recordCashIn(Order $order): void
    {
        // Gunakan updateOrInsert untuk cegah duplikat (idempotent)
        Cashflow::updateOrCreate(
            [
                'order_id' => $order->id,
                'type'     => 'in',
                'source'   => 'order',
            ],
            [
                'transaction_date' => now()->toDateString(),
                'category'         => 'Sales',
                'amount'           => $order->grand_total,
                'description'      => 'Penjualan pesanan #' . $order->order_number,
                'is_reversed'      => false,
            ]
        );
    }
}
