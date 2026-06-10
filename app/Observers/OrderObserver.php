<?php

namespace App\Observers;

use App\Models\Cashflow;
use App\Models\Order;
use App\Models\User;
use Filament\Notifications\Notification;

class OrderObserver
{
    /**
     * Saat pesanan baru dibuat (termasuk POS Manual yang langsung paid).
     */
    public function created(Order $order): void
    {
        // Notifikasi ke semua admin — pesanan baru masuk
        $this->sendOrderNotification(
            icon: 'heroicon-o-shopping-bag',
            iconColor: 'success',
            title: 'Pesanan Baru Masuk',
            body: "Pesanan #{$order->order_number} baru saja diterima." . ($order->grand_total ? ' Total: Rp ' . number_format($order->grand_total, 0, ',', '.') : ''),
        );

        if ($order->payment_status === 'paid') {
            $this->recordCashIn($order);
        }
    }

    /**
     * Saat pesanan di-update.
     */
    public function updated(Order $order): void
    {
        // Notif saat payment_status berubah ke paid
        if ($order->isDirty('payment_status') && $order->payment_status === 'paid') {
            $this->recordCashIn($order);

            $this->sendOrderNotification(
                icon: 'heroicon-o-banknotes',
                iconColor: 'success',
                title: 'Pembayaran Diterima',
                body: "Pesanan #{$order->order_number} telah lunas. Total: Rp " . number_format($order->grand_total, 0, ',', '.'),
            );
        }

        // Notif saat pesanan dibatalkan
        if ($order->isDirty('status') && $order->status === 'cancelled') {
            $this->sendOrderNotification(
                icon: 'heroicon-o-x-circle',
                iconColor: 'danger',
                title: 'Pesanan Dibatalkan',
                body: "Pesanan #{$order->order_number} telah dibatalkan.",
            );

            // Reversal cashflow
            $original = Cashflow::where('order_id', $order->id)
                ->where('type', 'in')
                ->where('source', 'order')
                ->where('is_reversed', false)
                ->first();

            if ($original) {
                $original->update([
                    'is_reversed'   => true,
                    'reversal_note' => 'Pesanan #' . $order->order_number . ' dibatalkan.',
                ]);

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
     * Kirim notifikasi ke semua admin panel.
     */
    private function sendOrderNotification(string $icon, string $iconColor, string $title, string $body): void
    {
        $admins = User::role('super_admin')->get();

        if ($admins->isEmpty()) {
            $admins = User::where('id', 2)->get(); // fallback ke super admin ID 2
        }

        foreach ($admins as $admin) {
            Notification::make()
                ->icon($icon)
                ->iconColor($iconColor)
                ->title($title)
                ->body($body)
                ->actions([
                    \Filament\Actions\Action::make('view')
                        ->label('Lihat Pesan' === $title ? 'Lihat Pesan' : 'Lihat Pesanan')
                        ->button()
                        ->url(route('filament.admin.e-commerce.resources.orders.index')),
                ])
                ->sendToDatabase($admin);
        }
    }

    /**
     * Helper: catat Cash In dari pesanan, cegah duplikat via updateOrCreate.
     */
    private function recordCashIn(Order $order): void
    {
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
