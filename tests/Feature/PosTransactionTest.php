<?php

namespace Tests\Feature;

use App\Models\Cashflow;
use App\Models\Order;
use App\Models\PosSession;
use App\Models\Product;
use App\Models\StockLog;
use App\Models\User;
use App\Services\PosTransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;
use Database\Seeders\RolesAndPermissionsSeeder;

class PosTransactionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed roles if necessary (Spatie permission)
        // $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_pos_transaction_creates_order_and_deducts_stock()
    {
        Mail::fake();

        // Create cashier
        $cashier = User::factory()->create();

        // Create POS Session
        $session = PosSession::create([
            'cashier_id' => $cashier->id,
            'opened_at' => now(),
            'opening_cash' => 100000,
            'status' => 'open',
        ]);

        // Create product
        $product = clone (new Product);
        $product->fill([
            'name' => 'Produk A',
            'slug' => 'produk-a',
            'sku' => 'PRD-A',
            'price' => 50000,
            'pos_price' => 45000,
            'stock' => 10,
            'is_active' => true,
            'channel_visibility' => 'both',
        ]);
        $product->save();

        // Execute service
        $service = new PosTransactionService();
        $payload = [
            'items' => [
                [
                    'product_id' => $product->id,
                    'product_variant_id' => null,
                    'quantity' => 2,
                ]
            ],
            'cashier_id' => $cashier->id,
            'pos_session_id' => $session->id,
            'cash_paid' => 100000,
            'cash_change' => 10000,
            'discount' => 0,
            'payment_method' => 'cash',
        ];

        $order = $service->completePosTransaction($payload);

        // Assert Order created correctly
        $this->assertNotNull($order);
        $this->assertEquals('pos', $order->source);
        $this->assertEquals(90000, $order->grand_total); // 45000 * 2
        $this->assertEquals(10000, $order->cash_change);

        // Assert Stock deducted
        $product->refresh();
        $this->assertEquals(8, $product->stock);

        // Assert StockLog recorded
        $this->assertDatabaseHas('stock_logs', [
            'product_id' => $product->id,
            'type' => 'out',
            'quantity_change' => -2,
        ]);

        // Assert Cashflow recorded (1 from Service)
        // Observer should be bypassed, so only 1 cashflow
        $cashflows = Cashflow::where('order_id', $order->id)->get();
        $this->assertCount(1, $cashflows);
        $this->assertEquals(90000, $cashflows->first()->amount);
        $this->assertEquals('pos', $cashflows->first()->source);
        $this->assertEquals('pos_sale', $cashflows->first()->category);

        // Assert No Emails Sent (Observer bypassed)
        Mail::assertNothingSent();
    }

    public function test_pos_transaction_with_discount_stores_discount_total()
    {
        $cashier = User::factory()->create();

        $session = PosSession::create([
            'cashier_id' => $cashier->id,
            'opened_at' => now(),
            'opening_cash' => 100000,
            'status' => 'open',
        ]);

        $product = clone (new Product);
        $product->fill([
            'name' => 'Produk Diskon',
            'slug' => 'produk-diskon',
            'sku' => 'PRD-DISC',
            'price' => 100000,
            'pos_price' => 100000,
            'stock' => 5,
            'is_active' => true,
            'channel_visibility' => 'both',
        ]);
        $product->save();

        $service = new PosTransactionService();
        $payload = [
            'items' => [
                [
                    'product_id' => $product->id,
                    'product_variant_id' => null,
                    'quantity' => 1,
                ]
            ],
            'cashier_id' => $cashier->id,
            'pos_session_id' => $session->id,
            'cash_paid' => 100000,
            'discount' => 15000,
            'payment_method' => 'cash',
        ];

        $order = $service->completePosTransaction($payload);

        $this->assertEquals(100000, $order->subtotal);
        $this->assertEquals(15000, $order->discount_total);
        $this->assertEquals(85000, $order->grand_total);
        $this->assertEquals(15000, $order->cash_change);

        // Verify receipt generator reads discount_total
        $escPosService = new \App\Services\EscPosService();
        $receiptBase64 = $escPosService->generateReceipt($order);
        $decodedReceipt = base64_decode($receiptBase64);

        $this->assertStringContainsString('Diskon', $decodedReceipt);
        $this->assertStringContainsString('-15.000', $decodedReceipt);
    }

    public function test_open_session_prevents_duplicate_active_sessions()
    {
        $cashier = User::factory()->create();

        // First session opened
        \Livewire\Livewire::actingAs($cashier)
            ->test(\App\Livewire\PosManager::class)
            ->set('openingCash', 50000)
            ->call('openSession')
            ->assertDispatched('session-opened');

        $this->assertEquals(1, PosSession::where('cashier_id', $cashier->id)->where('status', 'open')->count());

        // Attempting to open second session while first is active fails
        \Livewire\Livewire::actingAs($cashier)
            ->test(\App\Livewire\PosManager::class)
            ->set('openingCash', 100000)
            ->call('openSession')
            ->assertDispatched('notify');

        $this->assertEquals(1, PosSession::where('cashier_id', $cashier->id)->where('status', 'open')->count());
    }

    public function test_pos_transaction_increments_voucher_used_count_and_filters_exhausted_vouchers()
    {
        $cashier = User::factory()->create();

        $session = PosSession::create([
            'cashier_id' => $cashier->id,
            'opened_at' => now(),
            'opening_cash' => 100000,
            'status' => 'open',
        ]);

        $product = Product::create([
            'name' => 'Produk B',
            'slug' => 'produk-b',
            'sku' => 'PRD-B',
            'price' => 100000,
            'stock' => 10,
            'is_active' => true,
            'channel_visibility' => 'both',
        ]);

        // Voucher 1: Active, max_uses = 2, used_count = 1
        $voucherAvailable = \App\Models\Voucher::create([
            'name' => 'Diskon POS Quota Ada',
            'code' => 'QUOTAOK',
            'discount_type' => 'fixed',
            'discount_amount' => 10000,
            'max_uses' => 2,
            'used_count' => 1,
            'is_active' => true,
            'usable_channel' => 'both',
        ]);

        // Voucher 2: Active, max_uses = 2, used_count = 2 (Exhausted)
        $voucherExhausted = \App\Models\Voucher::create([
            'name' => 'Diskon POS Quota Habis',
            'code' => 'QUOTAFULL',
            'discount_type' => 'fixed',
            'discount_amount' => 20000,
            'max_uses' => 2,
            'used_count' => 2,
            'is_active' => true,
            'usable_channel' => 'both',
        ]);

        // Assert computed property vouchers filters out exhausted voucher
        $component = \Livewire\Livewire::actingAs($cashier)->test(\App\Livewire\PosManager::class);
        $vouchersList = $component->get('vouchers');

        $this->assertTrue($vouchersList->contains('id', $voucherAvailable->id));
        $this->assertFalse($vouchersList->contains('id', $voucherExhausted->id));

        // Complete POS Transaction with available voucher
        $service = new PosTransactionService();
        $payload = [
            'items' => [
                ['product_id' => $product->id, 'product_variant_id' => null, 'quantity' => 1]
            ],
            'cashier_id' => $cashier->id,
            'pos_session_id' => $session->id,
            'voucher_id' => $voucherAvailable->id,
            'discount' => 10000,
            'cash_paid' => 100000,
            'payment_method' => 'cash',
        ];

        $order = $service->completePosTransaction($payload);

        $this->assertEquals($voucherAvailable->id, $order->voucher_id);
        
        // Voucher used_count incremented from 1 to 2
        $voucherAvailable->refresh();
        $this->assertEquals(2, $voucherAvailable->used_count);

        // Attempting to use exhausted voucher via service throws exception
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('sudah mencapai batas maksimum kuota penggunaan');

        $payloadExhausted = $payload;
        $payloadExhausted['voucher_id'] = $voucherExhausted->id;
        $service->completePosTransaction($payloadExhausted);
    }

    public function test_z_report_includes_sales_breakdown_and_totals()
    {
        $cashier = User::factory()->create();

        $session = PosSession::create([
            'cashier_id' => $cashier->id,
            'opened_at' => now(),
            'opening_cash' => 200000,
            'status' => 'open',
        ]);

        $product = Product::create([
            'name' => 'Produk Z',
            'slug' => 'produk-z',
            'sku' => 'PRD-Z',
            'price' => 50000,
            'stock' => 20,
            'is_active' => true,
            'channel_visibility' => 'both',
        ]);

        $service = new PosTransactionService();
        // Sale 1: Cash 50,000
        $service->completePosTransaction([
            'items' => [['product_id' => $product->id, 'product_variant_id' => null, 'quantity' => 1]],
            'cashier_id' => $cashier->id,
            'pos_session_id' => $session->id,
            'cash_paid' => 50000,
            'payment_method' => 'cash',
        ]);

        // Sale 2: QRIS 50,000
        $service->completePosTransaction([
            'items' => [['product_id' => $product->id, 'product_variant_id' => null, 'quantity' => 1]],
            'cashier_id' => $cashier->id,
            'pos_session_id' => $session->id,
            'cash_paid' => 50000,
            'payment_method' => 'qris',
        ]);

        $escPos = new \App\Services\EscPosService();
        $zReportBase64 = $escPos->generateZReport($session);
        $zReportText = base64_decode($zReportBase64);

        $this->assertStringContainsString('Z-REPORT', $zReportText);
        $this->assertStringContainsString('Penjualan Tunai', $zReportText);
        $this->assertStringContainsString('Penjualan Non-Tunai', $zReportText);
        $this->assertStringContainsString('50.000', $zReportText);
        $this->assertStringContainsString('100.000', $zReportText);
    }
}
