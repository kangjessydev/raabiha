<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PosSession;
use App\Models\PosCustomer;
use App\Models\PosReturn;
use App\Models\PosReturnItem;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class PosDummyDataSeeder extends Seeder
{
    public function run()
    {
        // 1. Dapatkan atau buat Kasir 2
        $kasir2 = User::firstOrCreate(
            ['email' => 'kasir2@raabiha.com'],
            [
                'name' => 'Kasir 2',
                'password' => Hash::make('password'),
                'pos_pin' => Hash::make('123456'),
            ]
        );
        if (!$kasir2->hasRole('kasir')) {
            $kasir2->assignRole('kasir');
        }

        // Dapatkan Super Admin untuk supervisor void
        $admin = User::role('super_admin')->first() ?: $kasir2;

        // 2. Buat Data Pelanggan Dummy
        $customersData = [
            ['name' => 'Siti Rahma', 'phone' => '081234567891', 'stamp_count' => 8, 'total_spent' => 1450000],
            ['name' => 'Dewi Lestari', 'phone' => '081298765432', 'stamp_count' => 5, 'total_spent' => 890000],
            ['name' => 'Amina Yusuf', 'phone' => '085678901234', 'stamp_count' => 12, 'total_spent' => 2300000],
            ['name' => 'Rina Kusumawati', 'phone' => '087812345678', 'stamp_count' => 3, 'total_spent' => 620000],
            ['name' => 'Budi Santoso', 'phone' => '081399887766', 'stamp_count' => 2, 'total_spent' => 350000],
            ['name' => 'Nabila Putri', 'phone' => '082155443322', 'stamp_count' => 6, 'total_spent' => 980000],
        ];

        foreach ($customersData as $c) {
            PosCustomer::updateOrCreate(
                ['phone' => $c['phone']],
                [
                    'name' => $c['name'],
                    'stamp_count' => $c['stamp_count'],
                    'total_spent' => $c['total_spent'],
                ]
            );
        }

        // 3. Ambil Produk untuk Items Transaksi
        $products = Product::with('variants')->take(6)->get();
        if ($products->isEmpty()) {
            echo "Tidak ada produk di database. Silakan jalankan seeder produk dahulu.\n";
            return;
        }

        // 4. Hapus order POS terdahulu milik Kasir 2 agar data bersih dan tepat 10+ skenario
        $oldOrders = Order::where('source', 'pos')->get();
        foreach ($oldOrders as $o) {
            PosReturn::where('order_id', $o->id)->delete();
            OrderItem::where('order_id', $o->id)->delete();
            $o->delete();
        }
        PosSession::query()->delete();

        // 5. Buat Sesi Shift untuk Kasir 2 (Sesi Kemarin, Sesi 2 Hari Lalu, dan Sesi Aktif Hari Ini)
        $sessionClosed2 = PosSession::create([
            'cashier_id' => $kasir2->id,
            'opening_cash' => 150000,
            'opened_at' => Carbon::now()->subDays(2)->setHour(8)->setMinute(0),
            'closed_at' => Carbon::now()->subDays(2)->setHour(17)->setMinute(0),
            'actual_ending_cash' => 1120000,
            'notes' => 'Shift lancar, selisih +Rp 5.000 karena pecahan koin',
            'status' => 'closed',
        ]);

        $sessionClosed1 = PosSession::create([
            'cashier_id' => $kasir2->id,
            'opening_cash' => 150000,
            'opened_at' => Carbon::now()->subDay()->setHour(8)->setMinute(0),
            'closed_at' => Carbon::now()->subDay()->setHour(17)->setMinute(30),
            'actual_ending_cash' => 870000,
            'notes' => 'Shift aman, penutupan tepat waktu.',
            'status' => 'closed',
        ]);

        $sessionActive = PosSession::create([
            'cashier_id' => $kasir2->id,
            'opening_cash' => 200000,
            'opened_at' => Carbon::now()->setHour(8)->setMinute(15),
            'closed_at' => null,
            'actual_ending_cash' => null,
            'notes' => null,
            'status' => 'open',
        ]);

        // 6. Buat 10 Transaksi POS dengan Skenario Beragam

        $scenarios = [
            // Skenario 1: Active Session - Tunai Selesai, Pelanggan Umum
            [
                'session' => $sessionActive,
                'created_at' => Carbon::now()->subHours(4),
                'order_number' => 'POS-' . Carbon::now()->format('Ymd') . '-0001',
                'customer_name' => null,
                'customer_phone' => null,
                'payment_method' => 'cash',
                'discount_total' => 0,
                'status' => 'completed',
                'items' => [
                    ['product' => $products[0], 'qty' => 2, 'price' => 55000],
                ],
                'cash_paid' => 120000,
                'cash_change' => 10000,
            ],

            // Skenario 2: Active Session - QRIS Selesai, Pelanggan "Siti Rahma"
            [
                'session' => $sessionActive,
                'created_at' => Carbon::now()->subHours(3)->addMinutes(15),
                'order_number' => 'POS-' . Carbon::now()->format('Ymd') . '-0002',
                'customer_name' => 'Siti Rahma',
                'customer_phone' => '081234567891',
                'payment_method' => 'qris',
                'discount_total' => 0,
                'status' => 'completed',
                'items' => [
                    ['product' => $products[1] ?? $products[0], 'qty' => 1, 'price' => 145000],
                    ['product' => $products[2] ?? $products[0], 'qty' => 2, 'price' => 100000],
                ],
                'cash_paid' => 345000,
                'cash_change' => 0,
            ],

            // Skenario 3: Active Session - EDC Mandiri Selesai, Diskon Kupon Promo Rp 25.000, Pelanggan "Dewi Lestari"
            [
                'session' => $sessionActive,
                'created_at' => Carbon::now()->subHours(2)->addMinutes(30),
                'order_number' => 'POS-' . Carbon::now()->format('Ymd') . '-0003',
                'customer_name' => 'Dewi Lestari',
                'customer_phone' => '081298765432',
                'payment_method' => 'edc',
                'discount_total' => 25000,
                'status' => 'completed',
                'items' => [
                    ['product' => $products[0], 'qty' => 3, 'price' => 55000],
                ],
                'cash_paid' => 140000,
                'cash_change' => 0,
            ],

            // Skenario 4: Active Session - Tunai Selesai, Diskon Manual Rp 15.000
            [
                'session' => $sessionActive,
                'created_at' => Carbon::now()->subHours(2),
                'order_number' => 'POS-' . Carbon::now()->format('Ymd') . '-0004',
                'customer_name' => 'Pelanggan Umum',
                'customer_phone' => null,
                'payment_method' => 'cash',
                'discount_total' => 15000,
                'status' => 'completed',
                'items' => [
                    ['product' => $products[1] ?? $products[0], 'qty' => 1, 'price' => 145000],
                ],
                'cash_paid' => 150000,
                'cash_change' => 20000,
            ],

            // Skenario 5: Active Session - VOID (Dibatalkan Supervisor)
            [
                'session' => $sessionActive,
                'created_at' => Carbon::now()->subHour()->addMinutes(45),
                'order_number' => 'POS-' . Carbon::now()->format('Ymd') . '-0005',
                'customer_name' => null,
                'customer_phone' => null,
                'payment_method' => 'cash',
                'discount_total' => 0,
                'status' => 'cancelled',
                'void_by_id' => $admin->id,
                'void_reason' => 'Salah input item produk oleh kasir',
                'items' => [
                    ['product' => $products[0], 'qty' => 1, 'price' => 55000],
                ],
                'cash_paid' => 55000,
                'cash_change' => 0,
            ],

            // Skenario 6: Active Session - Transfer Bank Selesai, Pelanggan "Amina Yusuf"
            [
                'session' => $sessionActive,
                'created_at' => Carbon::now()->subHour()->addMinutes(10),
                'order_number' => 'POS-' . Carbon::now()->format('Ymd') . '-0006',
                'customer_name' => 'Amina Yusuf',
                'customer_phone' => '085678901234',
                'payment_method' => 'transfer',
                'discount_total' => 20000,
                'status' => 'completed',
                'items' => [
                    ['product' => $products[1] ?? $products[0], 'qty' => 2, 'price' => 145000],
                    ['product' => $products[0], 'qty' => 2, 'price' => 55000],
                ],
                'cash_paid' => 380000,
                'cash_change' => 0,
            ],

            // Skenario 7: Active Session - Tunai Selesai (Retur Sebagian)
            [
                'session' => $sessionActive,
                'created_at' => Carbon::now()->subMinutes(40),
                'order_number' => 'POS-' . Carbon::now()->format('Ymd') . '-0007',
                'customer_name' => 'Rina Kusumawati',
                'customer_phone' => '087812345678',
                'payment_method' => 'cash',
                'discount_total' => 0,
                'status' => 'completed',
                'items' => [
                    ['product' => $products[0], 'qty' => 2, 'price' => 55000],
                    ['product' => $products[1] ?? $products[0], 'qty' => 1, 'price' => 145000],
                ],
                'cash_paid' => 300000,
                'cash_change' => 45000,
                'has_return_partial' => true,
            ],

            // Skenario 8: Active Session - QRIS Selesai (Retur Total)
            [
                'session' => $sessionActive,
                'created_at' => Carbon::now()->subMinutes(20),
                'order_number' => 'POS-' . Carbon::now()->format('Ymd') . '-0008',
                'customer_name' => 'Budi Santoso',
                'customer_phone' => '081399887766',
                'payment_method' => 'qris',
                'discount_total' => 0,
                'status' => 'completed',
                'items' => [
                    ['product' => $products[2] ?? $products[0], 'qty' => 1, 'price' => 175000],
                ],
                'cash_paid' => 175000,
                'cash_change' => 0,
                'has_return_total' => true,
            ],

            // Skenario 9: Sesi Kemarin - Tunai Selesai
            [
                'session' => $sessionClosed1,
                'created_at' => Carbon::now()->subDay()->setHour(11)->setMinute(20),
                'order_number' => 'POS-' . Carbon::now()->subDay()->format('Ymd') . '-0001',
                'customer_name' => 'Nabila Putri',
                'customer_phone' => '082155443322',
                'payment_method' => 'cash',
                'discount_total' => 0,
                'status' => 'completed',
                'items' => [
                    ['product' => $products[1] ?? $products[0], 'qty' => 2, 'price' => 145000],
                ],
                'cash_paid' => 300000,
                'cash_change' => 10000,
            ],

            // Skenario 10: Sesi 2 Hari Lalu - Tunai Selesai
            [
                'session' => $sessionClosed2,
                'created_at' => Carbon::now()->subDays(2)->setHour(14)->setMinute(10),
                'order_number' => 'POS-' . Carbon::now()->subDays(2)->format('Ymd') . '-0001',
                'customer_name' => 'Siti Rahma',
                'customer_phone' => '081234567891',
                'payment_method' => 'cash',
                'discount_total' => 10000,
                'status' => 'completed',
                'items' => [
                    ['product' => $products[0], 'qty' => 4, 'price' => 55000],
                ],
                'cash_paid' => 220000,
                'cash_change' => 10000,
            ],
        ];

        foreach ($scenarios as $sc) {
            $subtotal = 0;
            foreach ($sc['items'] as $it) {
                $subtotal += $it['qty'] * $it['price'];
            }
            $grandTotal = max(0, $subtotal - $sc['discount_total']);

            $order = Order::create([
                'order_number' => $sc['order_number'],
                'cashier_id' => $kasir2->id,
                'pos_session_id' => $sc['session']->id,
                'customer_name' => $sc['customer_name'],
                'customer_phone' => $sc['customer_phone'],
                'subtotal' => $subtotal,
                'discount_total' => $sc['discount_total'],
                'grand_total' => $grandTotal,
                'cash_paid' => $sc['cash_paid'],
                'cash_change' => $sc['cash_change'],
                'payment_method' => $sc['payment_method'],
                'status' => $sc['status'],
                'source' => 'pos',
                'created_at' => $sc['created_at'],
                'updated_at' => $sc['created_at'],
                'void_by_id' => $sc['void_by_id'] ?? null,
                'void_reason' => $sc['void_reason'] ?? null,
            ]);

            foreach ($sc['items'] as $it) {
                $prod = $it['product'];
                $variant = $prod->variants->first();
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $prod->id,
                    'product_variant_id' => $variant ? $variant->id : null,
                    'name' => $prod->name . ($variant ? ' (' . $variant->name . ')' : ''),
                    'quantity' => $it['qty'],
                    'price' => $it['price'],
                    'total' => $it['qty'] * $it['price'],
                ]);
            }

            // Buat Record Retur jika ada
            if (!empty($sc['has_return_partial'])) {
                $ret = PosReturn::create([
                    'return_number' => 'RET-' . Carbon::now()->format('Ymd') . '-0001',
                    'order_id' => $order->id,
                    'pos_session_id' => $sc['session']->id,
                    'cashier_id' => $kasir2->id,
                    'type' => 'refund',
                    'reason' => '1 Pasmina Inner Jersey cacat jahitan samping',
                    'returned_subtotal' => 55000,
                    'net_amount' => 55000,
                    'refund_payment_method' => 'cash',
                    'created_at' => Carbon::now()->subMinutes(15),
                ]);

                $firstOrderItem = $order->items->first();
                PosReturnItem::create([
                    'pos_return_id' => $ret->id,
                    'product_id' => $firstOrderItem->product_id,
                    'product_variant_id' => $firstOrderItem->product_variant_id,
                    'type' => 'returned',
                    'quantity' => 1,
                    'price' => 55000,
                    'total' => 55000,
                ]);
            }

            if (!empty($sc['has_return_total'])) {
                $ret = PosReturn::create([
                    'return_number' => 'RET-' . Carbon::now()->format('Ymd') . '-0002',
                    'order_id' => $order->id,
                    'pos_session_id' => $sc['session']->id,
                    'cashier_id' => $kasir2->id,
                    'type' => 'refund',
                    'reason' => 'Salah ukuran & warna tidak cocok (Retur Total)',
                    'returned_subtotal' => 175000,
                    'net_amount' => 175000,
                    'refund_payment_method' => 'qris',
                    'created_at' => Carbon::now()->subMinutes(5),
                ]);

                foreach ($order->items as $oItem) {
                    PosReturnItem::create([
                        'pos_return_id' => $ret->id,
                        'product_id' => $oItem->product_id,
                        'product_variant_id' => $oItem->product_variant_id,
                        'type' => 'returned',
                        'quantity' => $oItem->quantity,
                        'price' => $oItem->price,
                        'total' => $oItem->total,
                    ]);
                }
            }
        }

        echo "Data Dummy POS untuk Kasir 2 berhasil dibuat!\n";
    }
}
