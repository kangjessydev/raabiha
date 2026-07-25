<?php

namespace Tests\Feature;

use App\Models\Cashflow;
use App\Models\Order;
use App\Models\PosReturn;
use App\Models\PosSession;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockLog;
use App\Models\User;
use App\Services\PosTransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PosReturnTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::create(['name' => 'owner', 'guard_name' => 'web']);
        Role::create(['name' => 'kasir', 'guard_name' => 'web']);
    }

    public function test_product_size_exchange_restocks_returned_item_and_deducts_new_item()
    {
        $cashier = User::factory()->create();
        $cashier->assignRole('kasir');

        $session = PosSession::create([
            'cashier_id' => $cashier->id,
            'opened_at' => now(),
            'opening_cash' => 100000,
            'status' => 'open',
        ]);

        // Product A with Variant Size M and Variant Size L
        $product = Product::create([
            'name' => 'Kemeja Linen Raabiha',
            'slug' => 'kemeja-linen-raabiha',
            'sku' => 'KMJ-LNN',
            'price' => 150000,
            'pos_price' => 150000,
            'stock' => 10,
            'is_active' => true,
            'channel_visibility' => 'both',
        ]);

        $variantM = ProductVariant::create([
            'product_id' => $product->id,
            'name' => 'Size M',
            'sku' => 'KMJ-LNN-M',
            'price' => 150000,
            'stock' => 5,
        ]);

        $variantL = ProductVariant::create([
            'product_id' => $product->id,
            'name' => 'Size L',
            'sku' => 'KMJ-LNN-L',
            'price' => 150000,
            'stock' => 5,
        ]);

        $service = new PosTransactionService();

        // 1. Initial sale: Customer buys Size M (qty 1)
        $order = $service->completePosTransaction([
            'items' => [
                [
                    'product_id' => $product->id,
                    'product_variant_id' => $variantM->id,
                    'quantity' => 1,
                ]
            ],
            'cashier_id' => $cashier->id,
            'pos_session_id' => $session->id,
            'cash_paid' => 150000,
            'payment_method' => 'cash',
        ]);

        $variantM->refresh();
        $this->assertEquals(4, $variantM->stock);

        // 2. Customer exchanges Size M for Size L (Same price, net_amount = 0)
        $posReturn = $service->processPosReturn([
            'order_id' => $order->id,
            'cashier_id' => $cashier->id,
            'pos_session_id' => $session->id,
            'type' => 'exchange',
            'reason' => 'Tukar ukuran M ke L',
            'returned_items' => [
                [
                    'product_id' => $product->id,
                    'product_variant_id' => $variantM->id,
                    'quantity' => 1,
                ]
            ],
            'exchanged_items' => [
                [
                    'product_id' => $product->id,
                    'product_variant_id' => $variantL->id,
                    'quantity' => 1,
                ]
            ],
        ]);

        $this->assertNotNull($posReturn);
        $this->assertEquals('exchange', $posReturn->type);
        $this->assertEquals(0, $posReturn->net_amount);

        // Restocked Size M: 4 + 1 = 5
        $variantM->refresh();
        $this->assertEquals(5, $variantM->stock);

        // Deducted Size L: 5 - 1 = 4
        $variantL->refresh();
        $this->assertEquals(4, $variantL->stock);

        // StockLogs recorded
        $this->assertDatabaseHas('stock_logs', [
            'product_variant_id' => $variantM->id,
            'type' => 'in',
            'reason' => 'pos_return',
        ]);
        $this->assertDatabaseHas('stock_logs', [
            'product_variant_id' => $variantL->id,
            'type' => 'out',
            'reason' => 'pos_exchange',
        ]);
    }

    public function test_product_exchange_with_extra_payment_creates_cash_in()
    {
        $cashier = User::factory()->create();
        $cashier->assignRole('kasir');

        $session = PosSession::create([
            'cashier_id' => $cashier->id,
            'opened_at' => now(),
            'opening_cash' => 100000,
            'status' => 'open',
        ]);

        $productOld = Product::create([
            'name' => 'Kaos Basic',
            'slug' => 'kaos-basic',
            'sku' => 'KOS-BSC',
            'price' => 50000,
            'pos_price' => 50000,
            'stock' => 10,
            'is_active' => true,
            'channel_visibility' => 'both',
        ]);

        $productNew = Product::create([
            'name' => 'Jaket Denim Premium',
            'slug' => 'jaket-denim-premium',
            'sku' => 'JKT-DNM',
            'price' => 200000,
            'pos_price' => 200000,
            'stock' => 5,
            'is_active' => true,
            'channel_visibility' => 'both',
        ]);

        $service = new PosTransactionService();

        $order = $service->completePosTransaction([
            'items' => [
                [
                    'product_id' => $productOld->id,
                    'product_variant_id' => null,
                    'quantity' => 1,
                ]
            ],
            'cashier_id' => $cashier->id,
            'pos_session_id' => $session->id,
            'cash_paid' => 50000,
            'payment_method' => 'cash',
        ]);

        // Exchange Kaos Basic (50k) for Jaket Denim (200k) -> Customer pays extra 150k
        $posReturn = $service->processPosReturn([
            'order_id' => $order->id,
            'cashier_id' => $cashier->id,
            'pos_session_id' => $session->id,
            'type' => 'exchange',
            'reason' => 'Tukar ke Jaket Denim',
            'returned_items' => [
                [
                    'product_id' => $productOld->id,
                    'product_variant_id' => null,
                    'quantity' => 1,
                ]
            ],
            'exchanged_items' => [
                [
                    'product_id' => $productNew->id,
                    'product_variant_id' => null,
                    'quantity' => 1,
                ]
            ],
        ]);

        $this->assertEquals(150000, $posReturn->net_amount);

        // Cashflow In recorded for extra payment
        $this->assertDatabaseHas('cashflows', [
            'category' => 'pos_exchange_pay',
            'type' => 'in',
            'amount' => 150000,
        ]);
    }

    public function test_product_return_refund_requires_valid_supervisor_pin_and_creates_cash_out()
    {
        $owner = User::factory()->create([
            'pos_pin' => Hash::make('888888'),
        ]);
        $owner->assignRole('owner');

        $cashier = User::factory()->create();
        $cashier->assignRole('kasir');

        $session = PosSession::create([
            'cashier_id' => $cashier->id,
            'opened_at' => now(),
            'opening_cash' => 200000,
            'status' => 'open',
        ]);

        $product = Product::create([
            'name' => 'Gamis Silk Elegance',
            'slug' => 'gamis-silk-elegance',
            'sku' => 'GMS-SLK',
            'price' => 250000,
            'pos_price' => 250000,
            'stock' => 5,
            'is_active' => true,
            'channel_visibility' => 'both',
        ]);

        $service = new PosTransactionService();

        $order = $service->completePosTransaction([
            'items' => [
                [
                    'product_id' => $product->id,
                    'product_variant_id' => null,
                    'quantity' => 1,
                ]
            ],
            'cashier_id' => $cashier->id,
            'pos_session_id' => $session->id,
            'cash_paid' => 250000,
            'payment_method' => 'cash',
        ]);

        // Attempt refund without PIN -> throws Exception
        $this->expectException(\Exception::class);
        $service->processPosReturn([
            'order_id' => $order->id,
            'cashier_id' => $cashier->id,
            'pos_session_id' => $session->id,
            'type' => 'refund',
            'reason' => 'Batal beli / Barang cacat',
            'returned_items' => [
                [
                    'product_id' => $product->id,
                    'product_variant_id' => null,
                    'quantity' => 1,
                ]
            ],
            'supervisor_id' => $owner->id,
            'supervisor_pin' => '000000', // Invalid PIN
        ]);
    }

    public function test_product_return_refund_succeeds_with_valid_supervisor_pin()
    {
        $owner = User::factory()->create([
            'pos_pin' => Hash::make('888888'),
        ]);
        $owner->assignRole('owner');

        $cashier = User::factory()->create();
        $cashier->assignRole('kasir');

        $session = PosSession::create([
            'cashier_id' => $cashier->id,
            'opened_at' => now(),
            'opening_cash' => 300000,
            'status' => 'open',
        ]);

        $product = Product::create([
            'name' => 'Gamis Silk Elegance',
            'slug' => 'gamis-silk-elegance-2',
            'sku' => 'GMS-SLK-2',
            'price' => 250000,
            'pos_price' => 250000,
            'stock' => 5,
            'is_active' => true,
            'channel_visibility' => 'both',
        ]);

        $service = new PosTransactionService();

        $order = $service->completePosTransaction([
            'items' => [
                [
                    'product_id' => $product->id,
                    'product_variant_id' => null,
                    'quantity' => 1,
                ]
            ],
            'cashier_id' => $cashier->id,
            'pos_session_id' => $session->id,
            'cash_paid' => 250000,
            'payment_method' => 'cash',
        ]);

        // Valid PIN -> succeeds
        $posReturn = $service->processPosReturn([
            'order_id' => $order->id,
            'cashier_id' => $cashier->id,
            'pos_session_id' => $session->id,
            'type' => 'refund',
            'reason' => 'Barang cacat jahitan',
            'returned_items' => [
                [
                    'product_id' => $product->id,
                    'product_variant_id' => null,
                    'quantity' => 1,
                ]
            ],
            'supervisor_id' => $owner->id,
            'supervisor_pin' => '888888', // Valid PIN
        ]);

        $this->assertEquals(-250000, $posReturn->net_amount);
        $this->assertEquals($owner->id, $posReturn->supervisor_id);

        // Restocked: 4 + 1 = 5
        $product->refresh();
        $this->assertEquals(5, $product->stock);

        // Cashflow Out recorded for refund
        $this->assertDatabaseHas('cashflows', [
            'order_id' => $order->id,
            'category' => 'pos_return_refund',
            'type' => 'out',
            'amount' => 250000,
        ]);
    }
}
