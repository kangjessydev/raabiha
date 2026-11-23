<?php

namespace Tests\Feature;

use App\Livewire\PosManager;
use App\Models\Order;
use App\Models\PosSession;
use App\Models\Product;
use App\Models\SiteSetting;
use App\Models\User;
use App\Services\PosTransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PosTransactionHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'kasir', 'guard_name' => 'web']);
    }

    private function createCashierAndSession(float $openingCash = 500000): array
    {
        $cashier = User::factory()->create(['role' => 'kasir']);
        $cashier->assignRole('kasir');

        $session = PosSession::create([
            'cashier_id' => $cashier->id,
            'opened_at' => now()->subHour(),
            'opening_cash' => $openingCash,
            'status' => 'open',
        ]);

        return [$cashier, $session];
    }

    public function test_console_tampered_zero_price_is_ignored_and_server_price_is_enforced(): void
    {
        [$cashier, $session] = $this->createCashierAndSession();

        $product = Product::create([
            'name' => 'Gamis Premium',
            'slug' => 'gamis-premium-'.uniqid(),
            'price' => 100000,
            'pos_price' => 100000,
            'stock' => 10,
            'is_active' => true,
            'has_variants' => false,
            'channel_visibility' => 'pos_only',
        ]);

        $order = app(PosTransactionService::class)->completePosTransaction([
            'cashier_id' => $cashier->id,
            'pos_session_id' => $session->id,
            'payment_method' => 'cash',
            'cash_paid' => 100000,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                    'price' => 0, // Tampered via browser console!
                    'original_price' => 0,
                ],
            ],
        ]);

        $this->assertEquals(100000.0, (float) $order->subtotal);
        $this->assertEquals(100000.0, (float) $order->grand_total);
        $this->assertEquals(0.0, (float) $order->discount_total);
        $this->assertEquals(100000.0, (float) $order->items()->first()->price);
    }

    public function test_underpaid_cash_and_split_payments_are_rejected_by_server(): void
    {
        [$cashier, $session] = $this->createCashierAndSession();

        $product = Product::create([
            'name' => 'Kerudung Silk',
            'slug' => 'kerudung-silk-'.uniqid(),
            'price' => 100000,
            'pos_price' => 100000,
            'stock' => 10,
            'is_active' => true,
            'has_variants' => false,
            'channel_visibility' => 'pos_only',
        ]);

        // 1. Underpaid cash
        try {
            app(PosTransactionService::class)->completePosTransaction([
                'cashier_id' => $cashier->id,
                'pos_session_id' => $session->id,
                'payment_method' => 'cash',
                'cash_paid' => 50000,
                'items' => [['product_id' => $product->id, 'quantity' => 1]],
            ]);
            $this->fail('Underpaid cash transaction should throw Exception.');
        } catch (\Exception $e) {
            $this->assertStringContainsString('kurang dari total belanja', $e->getMessage());
        }

        // 2. Underpaid split
        try {
            app(PosTransactionService::class)->completePosTransaction([
                'cashier_id' => $cashier->id,
                'pos_session_id' => $session->id,
                'payment_method' => 'split',
                'cash_paid' => 0,
                'payment_details' => [
                    'split_payments' => [
                        ['method' => 'cash', 'amount' => 40000],
                        ['method' => 'qris', 'amount' => 40000],
                    ],
                ],
                'items' => [['product_id' => $product->id, 'quantity' => 1]],
            ]);
            $this->fail('Underpaid split transaction should throw Exception.');
        } catch (\Exception $e) {
            $this->assertStringContainsString('Total pembayaran split kurang', $e->getMessage());
        }

        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(10, $product->fresh()->stock);
    }

    public function test_zero_or_negative_quantity_is_rejected_in_pos_checkout(): void
    {
        [$cashier, $session] = $this->createCashierAndSession();

        $product = Product::create([
            'name' => 'Tunik Katun',
            'slug' => 'tunik-katun-'.uniqid(),
            'price' => 80000,
            'pos_price' => 80000,
            'stock' => 5,
            'is_active' => true,
            'has_variants' => false,
            'channel_visibility' => 'pos_only',
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Jumlah item harus lebih dari 0.');

        app(PosTransactionService::class)->completePosTransaction([
            'cashier_id' => $cashier->id,
            'pos_session_id' => $session->id,
            'payment_method' => 'cash',
            'cash_paid' => 100000,
            'items' => [['product_id' => $product->id, 'quantity' => -2]],
        ]);
    }

    public function test_void_order_marks_stock_restored_and_deleting_order_does_not_double_restock(): void
    {
        [$cashier, $session] = $this->createCashierAndSession();
        SiteSetting::updateOrCreate(['key' => 'pos_require_pin_for_void'], ['value' => '0']);

        $product = Product::create([
            'name' => 'Abaya Hitam',
            'slug' => 'abaya-hitam-'.uniqid(),
            'price' => 200000,
            'pos_price' => 200000,
            'stock' => 10,
            'is_active' => true,
            'has_variants' => false,
            'channel_visibility' => 'pos_only',
        ]);

        $order = app(PosTransactionService::class)->completePosTransaction([
            'cashier_id' => $cashier->id,
            'pos_session_id' => $session->id,
            'payment_method' => 'cash',
            'cash_paid' => 400000,
            'items' => [['product_id' => $product->id, 'quantity' => 2]],
        ]);

        $this->assertSame(8, $product->fresh()->stock);

        Livewire::actingAs($cashier)
            ->test(PosManager::class)
            ->call('voidOrder', $order->id, null, null, 'Salah input')
            ->call('voidOrder', $order->id, null, null, 'Klik ganda');

        $order->refresh();
        $this->assertSame('cancelled', $order->status);
        $this->assertTrue((bool) $order->is_stock_restored);
        $this->assertSame(10, $product->fresh()->stock);

        // Deleting voided order should NOT restock again
        $order->delete();
        $this->assertSame(10, $product->fresh()->stock);
    }

    public function test_close_session_calculates_expected_cash_accurately_for_same_shift_void_and_split_payment(): void
    {
        [$cashier, $session] = $this->createCashierAndSession(500000);
        SiteSetting::updateOrCreate(['key' => 'pos_require_pin_for_void'], ['value' => '0']);

        $product = Product::create([
            'name' => 'Khimar Instan',
            'slug' => 'khimar-instan-'.uniqid(),
            'price' => 100000,
            'pos_price' => 100000,
            'stock' => 20,
            'is_active' => true,
            'has_variants' => false,
            'channel_visibility' => 'pos_only',
        ]);

        $service = app(PosTransactionService::class);

        // 1. Cash sale Rp 100.000 -> then VOID in SAME shift (net cash impact = 0)
        $voidedOrder = $service->completePosTransaction([
            'cashier_id' => $cashier->id,
            'pos_session_id' => $session->id,
            'payment_method' => 'cash',
            'cash_paid' => 100000,
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
        ]);

        // 2. Split sale Rp 100.000 (Rp 60.000 cash + Rp 40.000 QRIS) -> net cash drawer +60.000
        $service->completePosTransaction([
            'cashier_id' => $cashier->id,
            'pos_session_id' => $session->id,
            'payment_method' => 'split',
            'cash_paid' => 100000,
            'payment_details' => [
                'split_payments' => [
                    ['method' => 'cash', 'amount' => 60000],
                    ['method' => 'qris', 'amount' => 40000],
                ],
            ],
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
        ]);

        Livewire::actingAs($cashier)
            ->test(PosManager::class)
            ->call('voidOrder', $voidedOrder->id, null, null, 'Batal beli')
            ->set('actualEndingCash', 560000)
            ->call('closeSession');

        $session->refresh();
        $this->assertSame('closed', $session->status);
        $this->assertEquals(560000.0, (float) $session->expected_ending_cash);
        $this->assertEquals(0.0, (float) $session->difference_cash);
    }
}
