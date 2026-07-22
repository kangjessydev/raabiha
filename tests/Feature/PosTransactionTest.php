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

        // Assert No Emails Sent (Observer bypassed)
        Mail::assertNothingSent();
    }
}
