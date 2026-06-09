<?php

namespace App\Observers;

use App\Models\Cashflow;
use App\Models\Order;

class OrderObserver
{
    /**
     * Handle the Order "updated" event.
     * Auto-create Cash In record when an order is marked as paid.
     */
    public function updated(Order $order): void
    {
        // Cek apakah payment_status baru saja berubah menjadi 'paid'
        if ($order->isDirty('payment_status') && $order->payment_status === 'paid') {
            // Pastikan belum ada record cashflow untuk pesanan ini agar tidak duplikat
            $alreadyRecorded = Cashflow::where('order_id', $order->id)
                ->where('type', 'in')
                ->where('category', 'Sales')
                ->exists();

            if (! $alreadyRecorded) {
                Cashflow::create([
                    'transaction_date' => now()->toDateString(),
                    'type'             => 'in',
                    'category'         => 'Sales',
                    'amount'           => $order->grand_total,
                    'description'      => 'Pembayaran otomatis dari pesanan #' . $order->order_number,
                    'order_id'         => $order->id,
                ]);
            }
        }

        // Jika pesanan dibatalkan, hapus record Cash In terkait (jika ada)
        if ($order->isDirty('status') && $order->status === 'cancelled') {
            Cashflow::where('order_id', $order->id)
                ->where('type', 'in')
                ->delete();
        }
    }

    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order): void
    {
        // Auto-record jika langsung dibuat sebagai paid (misal: POS Manual)
        if ($order->payment_status === 'paid') {
            Cashflow::create([
                'transaction_date' => now()->toDateString(),
                'type'             => 'in',
                'category'         => 'Sales',
                'amount'           => $order->grand_total,
                'description'      => 'Pembayaran dari pesanan #' . $order->order_number,
                'order_id'         => $order->id,
            ]);
        }
    }

    public function deleted(Order $order): void {}
    public function restored(Order $order): void {}
    public function forceDeleted(Order $order): void {}
}
