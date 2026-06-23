<?php

namespace App\Observers;

use App\Models\Cashflow;
use App\Models\Order;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Mail;
use App\Mail\StoreMail;

class OrderObserver
{
    /**
     * Helper: Ambil email customer.
     */
    private function getCustomerEmail(Order $order): ?string
    {
        return $order->shipping_address['email'] ?? $order->user->email ?? null;
    }

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

        // 1. Kirim Email Konfirmasi ke Customer
        $customerEmail = $this->getCustomerEmail($order);
        if ($customerEmail) {
            try {
                Mail::to($customerEmail)->send(new StoreMail(
                    subject: "Konfirmasi Pesanan Anda #{$order->order_number}",
                    view: 'emails.order-details',
                    data: [
                        'order' => $order,
                        'greeting' => "Halo, " . ($order->shipping_address['name'] ?? $order->user->name ?? 'Pelanggan') . "!",
                        'messageBody' => "Terima kasih atas pesanan Anda. Kami telah menerima pesanan #{$order->order_number} dan saat ini sedang menunggu pembayaran."
                    ]
                ));
            } catch (\Exception $e) {
                logger()->error("Gagal mengirim email konfirmasi order ke customer: " . $e->getMessage());
            }
        }

        // 2. Kirim Email Notifikasi ke Admin/Owner/CS
        try {
            $adminRoles = ['super_admin', 'owner', 'cs'];
            $adminRecipients = User::role($adminRoles)->get();
            if ($adminRecipients->isEmpty()) {
                $adminRecipients = User::whereIn('id', [1, 2])->get();
            }

            foreach ($adminRecipients as $admin) {
                Mail::to($admin->email)->send(new StoreMail(
                    subject: "[Pesanan Baru] Pesanan #{$order->order_number} Telah Diterima",
                    view: 'emails.order-details',
                    data: [
                        'order' => $order,
                        'greeting' => "Halo, {$admin->name} (Tim Raabiha)!",
                        'messageBody' => "Pesanan baru #{$order->order_number} telah masuk ke sistem dan sedang menunggu pembayaran."
                    ]
                ));
            }
        } catch (\Exception $e) {
            logger()->error("Gagal mengirim email pesanan baru ke admin: " . $e->getMessage());
        }

        if ($order->payment_status === 'paid') {
            $this->recordCashIn($order);
        }
    }

    /**
     * Saat pesanan di-update.
     */
    public function updated(Order $order): void
    {
        $customerEmail = $this->getCustomerEmail($order);

        // Notif saat payment_status berubah ke paid
        if ($order->isDirty('payment_status') && $order->payment_status === 'paid') {
            $this->recordCashIn($order);

            $this->sendOrderNotification(
                icon: 'heroicon-o-banknotes',
                iconColor: 'success',
                title: 'Pembayaran Diterima',
                body: "Pesanan #{$order->order_number} telah lunas. Total: Rp " . number_format($order->grand_total, 0, ',', '.'),
            );

            // 1. Email Pembayaran Berhasil ke Customer
            if ($customerEmail) {
                try {
                    Mail::to($customerEmail)->send(new StoreMail(
                        subject: "Pembayaran Berhasil - Pesanan #{$order->order_number}",
                        view: 'emails.order-details',
                        data: [
                            'order' => $order,
                            'greeting' => "Halo, " . ($order->shipping_address['name'] ?? $order->user->name ?? 'Pelanggan') . "!",
                            'messageBody' => "Terima kasih! Pembayaran Anda untuk pesanan #{$order->order_number} senilai <strong>Rp" . number_format($order->grand_total, 0, ',', '.') . "</strong> telah berhasil kami terima. Kami akan segera memproses pengiriman pesanan Anda."
                        ]
                    ));
                } catch (\Exception $e) {
                    logger()->error("Gagal mengirim email lunas ke customer: " . $e->getMessage());
                }
            }

            // 2. Email Pembayaran Berhasil ke Admin/Finance/Owner
            try {
                $financeRoles = ['finance', 'super_admin', 'owner'];
                $financeRecipients = User::role($financeRoles)->get();
                if ($financeRecipients->isEmpty()) {
                    $financeRecipients = User::whereIn('id', [1, 2])->get();
                }

                foreach ($financeRecipients as $finance) {
                    Mail::to($finance->email)->send(new StoreMail(
                        subject: "[Pembayaran Lunas] Pesanan #{$order->order_number}",
                        view: 'emails.order-details',
                        data: [
                            'order' => $order,
                            'greeting' => "Halo, {$finance->name} (Tim Keuangan)!",
                            'messageBody' => "Pembayaran untuk pesanan #{$order->order_number} senilai Rp" . number_format($order->grand_total, 0, ',', '.') . " telah berhasil divalidasi dan lunas."
                        ]
                    ));
                }
            } catch (\Exception $e) {
                logger()->error("Gagal mengirim email lunas ke finance: " . $e->getMessage());
            }
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

            // Email pembatalan ke customer
            if ($customerEmail) {
                try {
                    Mail::to($customerEmail)->send(new StoreMail(
                        subject: "Pembatalan Pesanan #{$order->order_number}",
                        view: 'emails.order-details',
                        data: [
                            'order' => $order,
                            'greeting' => "Halo, " . ($order->shipping_address['name'] ?? $order->user->name ?? 'Pelanggan') . "!",
                            'messageBody' => "Pesanan Anda #{$order->order_number} telah dibatalkan. Jika Anda sudah melakukan transfer pembayaran, silakan hubungi tim CS kami untuk bantuan proses pengembalian dana."
                        ]
                    ));
                } catch (\Exception $e) {
                    logger()->error("Gagal mengirim email pembatalan ke customer: " . $e->getMessage());
                }
            }
        }

        // Notif saat pesanan dikirim (awb_number set atau status berubah ke 'sent')
        $isSentDirty = $order->isDirty('status') && $order->status === 'sent';
        $isAwbDirty = $order->isDirty('awb_number') && !empty($order->awb_number) && $order->isDirty('awb_number');

        if (($isSentDirty || $isAwbDirty) && $customerEmail) {
            try {
                Mail::to($customerEmail)->send(new StoreMail(
                    subject: "Pesanan Anda #{$order->order_number} Telah Dikirim",
                    view: 'emails.order-details',
                    data: [
                        'order' => $order,
                        'greeting' => "Halo, " . ($order->shipping_address['name'] ?? $order->user->name ?? 'Pelanggan') . "!",
                        'messageBody' => "Kabar gembira! Pesanan Anda #{$order->order_number} telah dikirim menggunakan kurir <strong>" . strtoupper($order->courier ?? '') . "</strong> dengan nomor resi pengiriman (AWB): <strong>" . ($order->awb_number ?? '-') . "</strong>. Silakan lacak pengiriman Anda secara berkala."
                    ]
                ));
            } catch (\Exception $e) {
                logger()->error("Gagal mengirim email resi pengiriman ke customer: " . $e->getMessage());
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
