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
     * Helper: Ambil email admin/toko untuk notifikasi.
     */
    private function getAdminEmails(): array
    {
        $emails = [];

        // 1. Dari SiteSetting 'store_email'
        $siteEmail = \App\Models\SiteSetting::where('key', 'store_email')->value('value');
        if ($siteEmail && filter_var($siteEmail, FILTER_VALIDATE_EMAIL)) {
            $emails[] = $siteEmail;
        }

        // 2. Tambahan: Super Admin, Owner, Finance
        try {
            $adminRoles = ['super_admin', 'owner', 'finance'];
            $admins = User::role($adminRoles)->get();
            foreach ($admins as $admin) {
                if ($admin->email && filter_var($admin->email, FILTER_VALIDATE_EMAIL)) {
                    $emails[] = $admin->email;
                }
            }
        } catch (\Exception $e) {
            // Abaikan jika relasi/role Spatie bermasalah
        }

        // 3. Fallback jika kosong sama sekali
        if (empty($emails)) {
            $fallbacks = User::whereIn('id', [1, 2])->get();
            foreach ($fallbacks as $f) {
                if ($f->email && filter_var($f->email, FILTER_VALIDATE_EMAIL)) {
                    $emails[] = $f->email;
                }
            }
        }

        return array_unique($emails);
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
                $emailData = [
                    'order' => $order,
                    'greeting' => "Halo, " . ($order->shipping_address['name'] ?? $order->user->name ?? 'Pelanggan') . "!",
                    'messageBody' => "Terima kasih atas pesanan Anda. Kami telah menerima pesanan #{$order->order_number} dan saat ini sedang menunggu pembayaran."
                ];

                if ($order->payment_status === 'pending' && $order->payment_url) {
                    $emailData['actionUrl'] = $order->payment_url;
                    $emailData['actionText'] = 'Selesaikan Pembayaran';
                }

                Mail::to($customerEmail)->send(new StoreMail(
                    subject: "Konfirmasi Pesanan Anda #{$order->order_number}",
                    view: 'emails.order-details',
                    data: $emailData
                ));
            } catch (\Exception $e) {
                logger()->error("Gagal mengirim email konfirmasi order ke customer: " . $e->getMessage());
            }
        }

        // 2. Kirim Email Notifikasi ke Admin/Owner/CS
        try {
            $adminEmails = $this->getAdminEmails();
            foreach ($adminEmails as $email) {
                $recipientName = User::where('email', $email)->value('name') ?? 'Admin';
                Mail::to($email)->send(new StoreMail(
                    subject: "[Pesanan Baru] Pesanan #{$order->order_number} Telah Diterima",
                    view: 'emails.order-details',
                    data: [
                        'order' => $order,
                        'greeting' => "Halo, {$recipientName} (Tim Raabiha)!",
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
                $adminEmails = $this->getAdminEmails();
                foreach ($adminEmails as $email) {
                    $recipientName = User::where('email', $email)->value('name') ?? 'Admin';
                    Mail::to($email)->send(new StoreMail(
                        subject: "[Pembayaran Lunas] Pesanan #{$order->order_number}",
                        view: 'emails.order-details',
                        data: [
                            'order' => $order,
                            'greeting' => "Halo, {$recipientName} (Tim Raabiha)!",
                            'messageBody' => "Pembayaran untuk pesanan #{$order->order_number} senilai Rp" . number_format($order->grand_total, 0, ',', '.') . " telah berhasil divalidasi dan lunas."
                        ]
                    ));
                }
            } catch (\Exception $e) {
                logger()->error("Gagal mengirim email lunas ke admin: " . $e->getMessage());
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
                    $isFailedPayment = $order->payment_status === 'failed';
                    $subject = $isFailedPayment ? "Batas Waktu Pembayaran Habis - Pesanan #{$order->order_number}" : "Pembatalan Pesanan #{$order->order_number}";
                    $messageBody = $isFailedPayment 
                        ? "Batas waktu pembayaran untuk pesanan Anda <strong>#{$order->order_number}</strong> telah habis (kadaluarsa/gagal). Pesanan Anda secara otomatis dibatalkan oleh sistem. Silakan lakukan pemesanan ulang jika Anda masih ingin membeli produk tersebut."
                        : "Pesanan Anda <strong>#{$order->order_number}</strong> telah dibatalkan. Jika Anda sudah melakukan transfer pembayaran, silakan hubungi tim CS kami untuk bantuan proses pengembalian dana.";

                    Mail::to($customerEmail)->send(new StoreMail(
                        subject: $subject,
                        view: 'emails.order-details',
                        data: [
                            'order' => $order,
                            'greeting' => "Halo, " . ($order->shipping_address['name'] ?? $order->user->name ?? 'Pelanggan') . "!",
                            'messageBody' => $messageBody
                        ]
                    ));
                } catch (\Exception $e) {
                    logger()->error("Gagal mengirim email pembatalan ke customer: " . $e->getMessage());
                }
            }

            // Email pembatalan ke admin
            try {
                $adminEmails = $this->getAdminEmails();
                foreach ($adminEmails as $email) {
                    $recipientName = User::where('email', $email)->value('name') ?? 'Admin';
                    $isFailedPayment = $order->payment_status === 'failed';
                    $subject = $isFailedPayment ? "[Pesanan Gagal/Expired] Pesanan #{$order->order_number}" : "[Pesanan Dibatalkan] Pesanan #{$order->order_number}";
                    $messageBody = $isFailedPayment 
                        ? "Batas waktu pembayaran untuk pesanan #{$order->order_number} telah habis. Pesanan dibatalkan secara otomatis oleh sistem."
                        : "Pesanan #{$order->order_number} telah dibatalkan.";

                    Mail::to($email)->send(new StoreMail(
                        subject: $subject,
                        view: 'emails.order-details',
                        data: [
                            'order' => $order,
                            'greeting' => "Halo, {$recipientName} (Tim Raabiha)!",
                            'messageBody' => $messageBody
                        ]
                    ));
                }
            } catch (\Exception $e) {
                logger()->error("Gagal mengirim email pembatalan ke admin: " . $e->getMessage());
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
