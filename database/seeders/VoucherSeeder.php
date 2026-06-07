<?php

namespace Database\Seeders;

use App\Models\Voucher;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class VoucherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $vouchers = [
            [
                'name' => 'Diskon Sambut Gajian 50RB',
                'code' => 'GAJIAN50',
                'discount_type' => 'fixed',
                'discount_amount' => 50000,
                'min_purchase' => 200000,
                'min_items' => 0,
                'max_discount' => null,
                'is_shipping_voucher' => false,
                'is_stackable' => false,
                'exclude_resellers' => true,
                'max_uses' => 100,
                'starts_at' => $now,
                'expires_at' => $now->copy()->addDays(7),
                'is_active' => true,
            ],
            [
                'name' => 'Diskon Spesial 20%',
                'code' => 'SPESIAL20',
                'discount_type' => 'percent',
                'discount_amount' => 20,
                'min_purchase' => 150000,
                'min_items' => 0,
                'max_discount' => 100000, // Max Rp 100.000
                'is_shipping_voucher' => false,
                'is_stackable' => false,
                'exclude_resellers' => true,
                'max_uses' => null,
                'starts_at' => clone $now,
                'expires_at' => clone $now->copy()->addDays(30),
                'is_active' => true,
            ],
            [
                'name' => 'Gratis Ongkir Pulau Jawa',
                'code' => 'ONGKIRJAWA',
                'discount_type' => 'fixed',
                'discount_amount' => 20000, // Potong ongkir 20rb
                'min_purchase' => 100000,
                'min_items' => 0,
                'max_discount' => null,
                'is_shipping_voucher' => true,
                'is_stackable' => true, // Bisa digabung dengan diskon produk
                'exclude_resellers' => false, // Reseller boleh pakai gratis ongkir
                'max_uses' => 500,
                'starts_at' => clone $now,
                'expires_at' => clone $now->copy()->addDays(14),
                'is_active' => true,
            ],
            [
                'name' => 'Diskon Beli Banyak (Min 3 Item)',
                'code' => 'BORONG3',
                'discount_type' => 'fixed',
                'discount_amount' => 75000,
                'min_purchase' => 0,
                'min_items' => 3, // Minimal 3 item di keranjang
                'max_discount' => null,
                'is_shipping_voucher' => false,
                'is_stackable' => false,
                'exclude_resellers' => true,
                'max_uses' => 50,
                'starts_at' => clone $now,
                'expires_at' => clone $now->copy()->addDays(30),
                'is_active' => true,
            ],
        ];

        foreach ($vouchers as $voucher) {
            Voucher::create($voucher);
        }
    }
}
