<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RefundRequest extends Model
{
    use HasFactory;

    protected static function booted()
    {
        static::created(function (RefundRequest $refundRequest) {
            $orderNumber = $refundRequest->order->order_number ?? 'Pesanan';
            $customerName = $refundRequest->user->name ?? 'Pelanggan';

            // Notifikasi ke Admin/Owner/Finance
            try {
                $emails = [];
                $siteEmail = \App\Models\SiteSetting::where('key', 'store_email')->value('value');
                if ($siteEmail && filter_var($siteEmail, FILTER_VALIDATE_EMAIL)) {
                    $emails[] = $siteEmail;
                }
                try {
                    $existingRoles = \Spatie\Permission\Models\Role::whereIn('name', ['super_admin', 'owner', 'finance'])->pluck('name')->toArray();
                    $users = !empty($existingRoles) ? User::role($existingRoles)->get() : collect();
                    foreach ($users as $u) {
                        if ($u->email && filter_var($u->email, FILTER_VALIDATE_EMAIL)) {
                            $emails[] = $u->email;
                        }
                    }
                } catch (\Exception $roleEx) {
                    // Abaikan jika relasi/role Spatie bermasalah
                }
                if (empty($emails)) {
                    $fallbackUsers = User::whereIn('id', [1, 2])->get();
                    foreach ($fallbackUsers as $u) {
                        if ($u->email && filter_var($u->email, FILTER_VALIDATE_EMAIL)) {
                            $emails[] = $u->email;
                        }
                    }
                }
                $emails = array_unique($emails);

                foreach ($emails as $email) {
                    $recipientName = User::where('email', $email)->value('name') ?? 'Admin';
                    \Illuminate\Support\Facades\Mail::to($email)->send(new \App\Mail\StoreMail(
                        subject: "⚠️ [Pengajuan Refund] Permintaan Refund Baru - Pesanan #{$orderNumber}",
                        view: 'emails.layout',
                        data: [
                            'greeting' => "Halo, {$recipientName}!",
                            'messageBody' => "Pelanggan <strong>{$customerName}</strong> mengajukan permintaan pengembalian dana (refund) untuk pesanan <strong>#{$orderNumber}</strong> sebesar <strong>Rp" . number_format($refundRequest->refund_amount, 0, ',', '.') . "</strong>.<br><br>Alasan Pengajuan:<br><em>\"{$refundRequest->reason}\"</em>.<br><br>Detail Rekening:<br>Bank: {$refundRequest->bank_name}<br>Nama Rekening: {$refundRequest->bank_account_name}<br>No Rekening: {$refundRequest->bank_account_number}<br><br>Silakan masuk ke panel admin untuk memproses refund ini.",
                            'actionUrl' => route('filament.admin.e-commerce.resources.refund-requests.index'),
                            'actionText' => 'Tinjau Pengajuan Refund'
                        ]
                    ));
                }
            } catch (\Exception $e) {
                logger()->error("Gagal mengirim email pengajuan refund ke admin: " . $e->getMessage());
            }

            // Notifikasi ke Customer
            $customerEmail = $refundRequest->user->email ?? $refundRequest->order->shipping_address['email'] ?? null;
            if ($customerEmail) {
                try {
                    \Illuminate\Support\Facades\Mail::to($customerEmail)->send(new \App\Mail\StoreMail(
                        subject: "📩 [Pengajuan Refund] Permintaan Refund Diterima - Pesanan #{$orderNumber}",
                        view: 'emails.layout',
                        data: [
                            'greeting' => "Halo, " . ($refundRequest->user->name ?? 'Pelanggan') . "!",
                            'messageBody' => "Kami telah menerima permohonan pengembalian dana (refund) untuk pesanan <strong>#{$orderNumber}</strong> sebesar <strong>Rp" . number_format($refundRequest->refund_amount, 0, ',', '.') . "</strong>.<br><br>Rincian Rekening Anda:<br>Bank: {$refundRequest->bank_name}<br>Nama Rekening: {$refundRequest->bank_account_name}<br>No Rekening: {$refundRequest->bank_account_number}<br><br>Pengajuan Anda sedang diproses dan diverifikasi oleh tim kami. Kami akan segera mengirimkan email konfirmasi setelah status pengajuan Anda diperbarui.",
                        ]
                    ));
                } catch (\Exception $e) {
                    logger()->error("Gagal mengirim email pengajuan refund ke customer: " . $e->getMessage());
                }
            }
        });

        static::updated(function (RefundRequest $refundRequest) {
            $orderNumber = $refundRequest->order->order_number ?? 'Pesanan';

            if ($refundRequest->isDirty('status')) {
                if ($refundRequest->status === 'completed') {
                    if ($refundRequest->order) {
                        $refundRequest->order->update([
                            'payment_status' => 'refunded',
                        ]);
                    }

                    // Email ke customer: Refund disetujui & selesai
                    $customerEmail = $refundRequest->user->email ?? $refundRequest->order->shipping_address['email'] ?? null;
                    if ($customerEmail) {
                        try {
                            \Illuminate\Support\Facades\Mail::to($customerEmail)->send(new \App\Mail\StoreMail(
                                subject: "✅ [Refund Selesai] Pengembalian Dana Disetujui - Pesanan #{$orderNumber}",
                                view: 'emails.layout',
                                data: [
                                    'greeting' => "Halo, " . ($refundRequest->user->name ?? 'Pelanggan') . "!",
                                    'messageBody' => "Kabar baik, pengajuan pengembalian dana (refund) Anda untuk pesanan <strong>#{$orderNumber}</strong> sebesar <strong>Rp" . number_format($refundRequest->refund_amount, 0, ',', '.') . "</strong> telah disetujui dan berhasil diproses oleh tim kami.<br><br>Dana telah dikirim ke rekening berikut:<br>Bank: {$refundRequest->bank_name}<br>Nama Rekening: {$refundRequest->bank_account_name}<br>No Rekening: {$refundRequest->bank_account_number}<br><br>Terima kasih atas kesabaran Anda bertransaksi di Raabiha."
                                ]
                            ));
                        } catch (\Exception $e) {
                            logger()->error("Gagal mengirim email refund selesai ke customer: " . $e->getMessage());
                        }
                    }
                } elseif ($refundRequest->status === 'rejected') {
                    // Email ke customer: Refund ditolak
                    $customerEmail = $refundRequest->user->email ?? $refundRequest->order->shipping_address['email'] ?? null;
                    if ($customerEmail) {
                        try {
                            \Illuminate\Support\Facades\Mail::to($customerEmail)->send(new \App\Mail\StoreMail(
                                subject: "❌ [Refund Ditolak] Pengembalian Dana Ditolak - Pesanan #{$orderNumber}",
                                view: 'emails.layout',
                                data: [
                                    'greeting' => "Halo, " . ($refundRequest->user->name ?? 'Pelanggan') . "!",
                                    'messageBody' => "Mohon maaf, pengajuan pengembalian dana (refund) Anda untuk pesanan <strong>#{$orderNumber}</strong> ditolak oleh tim verifikasi kami dengan catatan berikut:<br><br><em>\"" . ($refundRequest->admin_notes ?? 'Tidak memenuhi syarat pengembalian.') . "\"</em>.<br><br>Jika Anda membutuhkan bantuan lebih lanjut, silakan hubungi Customer Service kami."
                                ]
                            ));
                        } catch (\Exception $e) {
                            logger()->error("Gagal mengirim email refund ditolak ke customer: " . $e->getMessage());
                        }
                    }
                }
            }
        });
    }

    protected $fillable = [
        'user_id',
        'order_id',
        'reason',
        'description',
        'refund_amount',
        'status',
        'bank_name',
        'bank_account_name',
        'bank_account_number',
        'proof_image',
        'admin_notes',
    ];

    protected $casts = [
        'refund_amount' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
