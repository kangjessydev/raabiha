<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use App\Models\Voucher;
use Exception;

class VoucherService
{
    /**
     * Memvalidasi apakah sebuah voucher dapat digunakan oleh pelanggan saat ini.
     *
     * @return array{valid: bool, message: ?string}
     */
    public function validateVoucher(
        Voucher $voucher,
        float $subtotal,
        int $itemCount = 1,
        ?User $user = null,
        ?string $guestEmail = null,
        string $channel = 'ecommerce'
    ): array {
        if (!$voucher->is_active) {
            return ['valid' => false, 'message' => "Kode voucher '{$voucher->code}' tidak aktif."];
        }

        if ($voucher->starts_at && $voucher->starts_at->isFuture()) {
            return ['valid' => false, 'message' => "Kode voucher '{$voucher->code}' belum dapat digunakan."];
        }

        if ($voucher->expires_at && $voucher->expires_at->isPast()) {
            return ['valid' => false, 'message' => "Kode voucher '{$voucher->code}' sudah kadaluarsa."];
        }

        if ($channel === 'ecommerce' && $voucher->usable_channel === 'pos_only') {
            return ['valid' => false, 'message' => "Kode voucher '{$voucher->code}' hanya berlaku untuk kasir/POS."];
        }

        if ($channel === 'pos' && $voucher->usable_channel === 'ecommerce_only') {
            return ['valid' => false, 'message' => "Kode voucher '{$voucher->code}' hanya berlaku untuk toko online."];
        }

        if ($voucher->max_uses > 0 && $voucher->used_count >= $voucher->max_uses) {
            return ['valid' => false, 'message' => "Kuota voucher '{$voucher->code}' telah habis."];
        }

        if ($voucher->min_items > 0 && $itemCount < $voucher->min_items) {
            return ['valid' => false, 'message' => "Minimal belanja {$voucher->min_items} item untuk menggunakan voucher ini."];
        }

        if ($voucher->min_purchase > 0 && $subtotal < $voucher->min_purchase) {
            $formattedMin = number_format($voucher->min_purchase, 0, ',', '.');
            return ['valid' => false, 'message' => "Minimal belanja Rp {$formattedMin} untuk voucher '{$voucher->code}'."];
        }

        if ($voucher->exclude_resellers && $user && $user->hasRole('reseller')) {
            return ['valid' => false, 'message' => 'Voucher ini tidak berlaku untuk akun Reseller.'];
        }

        if (!empty($voucher->specific_users)) {
            $email = $user ? $user->email : $guestEmail;
            if (!$email || !in_array($email, $voucher->specific_users)) {
                return ['valid' => false, 'message' => 'Voucher ini hanya berlaku untuk akun tertentu.'];
            }
        }

        // Batas pemakaian per pengguna (berdasarkan user_id atau email)
        if ($voucher->max_uses_per_user > 0) {
            $userUsageQuery = Order::where('voucher_id', $voucher->id)
                ->where(function ($q) {
                    $q->where('payment_status', '!=', 'cancelled')
                      ->where('status', '!=', 'cancelled');
                });

            $emailToCheck = $user ? $user->email : $guestEmail;

            if ($user) {
                $userUsageQuery->where(function ($q) use ($user, $emailToCheck) {
                    $q->where('user_id', $user->id);
                    if ($emailToCheck) {
                        $q->orWhere('shipping_address->email', $emailToCheck);
                    }
                });
            } elseif ($emailToCheck) {
                $userUsageQuery->where('shipping_address->email', $emailToCheck);
            } else {
                $userUsageQuery = null;
            }

            if ($userUsageQuery && $userUsageQuery->count() >= $voucher->max_uses_per_user) {
                return [
                    'valid' => false,
                    'message' => "Anda sudah mencapai batas pemakaian ({$voucher->max_uses_per_user}x) untuk voucher '{$voucher->code}'."
                ];
            }
        }

        return ['valid' => true, 'message' => null];
    }

    /**
     * Menghitung nilai diskon deterministik dari voucher model terhadap base amount.
     */
    public function calculateDiscount(Voucher $voucher, float $baseAmount): float
    {
        if ($baseAmount <= 0) {
            return 0;
        }

        $discount = 0;
        if ($voucher->discount_type === 'percent') {
            $discount = $baseAmount * ((float) $voucher->discount_amount / 100);
            if ($voucher->max_discount > 0 && $discount > (float) $voucher->max_discount) {
                $discount = (float) $voucher->max_discount;
            }
        } else {
            $discount = (float) $voucher->discount_amount;
        }

        return max(0, min($discount, $baseAmount));
    }

    /**
     * Memvalidasi dan mereposisi diskon beberapa voucher secara deterministik
     * langsung dari database model (bisa dengan lockForUpdate untuk transaksi checkout).
     *
     * @param array<int, array<string, mixed>> $appliedVouchersData
     * @return array{
     *     product_discount: float,
     *     shipping_discount: float,
     *     total_discount: float,
     *     verified_vouchers: array<int, Voucher>
     * }
     * @throws Exception
     */
    public function verifyAndCalculate(
        array $appliedVouchersData,
        float $subtotal,
        float $shippingCost,
        int $itemCount = 1,
        ?User $user = null,
        ?string $guestEmail = null,
        bool $lockForUpdate = false
    ): array {
        $recalculatedProductDiscount = 0;
        $recalculatedShippingDiscount = 0;
        $verifiedVouchers = [];

        foreach ($appliedVouchersData as $vData) {
            $voucherCode = $vData['code'] ?? null;
            if (!$voucherCode) {
                continue;
            }

            $query = Voucher::where('code', $voucherCode);
            if ($lockForUpdate) {
                $query->lockForUpdate();
            }
            $voucherModel = $query->first();

            if (!$voucherModel) {
                throw new Exception("Voucher '{$voucherCode}' tidak ditemukan atau sudah tidak valid.");
            }

            $validation = $this->validateVoucher(
                $voucherModel,
                $subtotal,
                $itemCount,
                $user,
                $guestEmail
            );

            if (!$validation['valid']) {
                throw new Exception($validation['message']);
            }

            $discount = $this->calculateDiscount($voucherModel, $subtotal);

            if ($voucherModel->is_shipping_voucher) {
                $remainingShipping = max(0, $shippingCost - $recalculatedShippingDiscount);
                $shipDiscount = min($remainingShipping, $discount);
                $recalculatedShippingDiscount += $shipDiscount;
            } else {
                $remainingProduct = max(0, $subtotal - $recalculatedProductDiscount);
                $prodDiscount = min($remainingProduct, $discount);
                $recalculatedProductDiscount += $prodDiscount;
            }

            $verifiedVouchers[] = $voucherModel;
        }

        return [
            'product_discount' => $recalculatedProductDiscount,
            'shipping_discount' => $recalculatedShippingDiscount,
            'total_discount' => $recalculatedProductDiscount + $recalculatedShippingDiscount,
            'verified_vouchers' => $verifiedVouchers,
        ];
    }
}
