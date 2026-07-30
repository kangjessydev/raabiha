<?php

namespace Tests\Feature;

use App\Models\Cashflow;
use App\Models\Category;
use App\Models\Order;
use App\Models\PosCustomer;
use App\Models\PosDebtPayment;
use App\Models\PosEventPromotion;
use App\Models\PosHeldCart;
use App\Models\PosSession;
use App\Models\Product;
use App\Models\StockLog;
use App\Models\User;
use App\Services\PosTransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PosNewFeaturesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 1. Test Custom Product Fast Entry (One-off item & persistent catalog save)
     */
    public function test_custom_product_checkout_and_persistent_catalog_save()
    {
        Mail::fake();

        $cashier = User::factory()->create();
        $session = PosSession::create([
            'cashier_id' => $cashier->id,
            'opened_at' => now(),
            'opening_cash' => 100000,
            'status' => 'open',
        ]);

        $service = new PosTransactionService();
        $payload = [
            'cashier_id' => $cashier->id,
            'pos_session_id' => $session->id,
            'payment_method' => 'cash',
            'cash_paid' => 300000,
            'customer_name' => 'Budi Nego',
            'items' => [
                [
                    'is_custom' => true,
                    'product_id' => null,
                    'name' => 'Jaket Impor Korea Nego',
                    'price' => 220000,
                    'purchase_price' => 150000,
                    'quantity' => 1,
                    'save_to_catalog' => true,
                ],
            ],
        ];

        $order = $service->completePosTransaction($payload);

        $this->assertEquals(220000, $order->grand_total);
        $this->assertCount(1, $order->items);
        $this->assertEquals('Jaket Impor Korea Nego', $order->items->first()->name);
        $this->assertEquals(150000, $order->items->first()->purchase_price);

        // Verify product was persisted to POS catalog (Majoo feature)
        $catalogProduct = Product::where('name', 'Jaket Impor Korea Nego')->first();
        $this->assertNotNull($catalogProduct);
        $this->assertEquals('pos_only', $catalogProduct->channel_visibility);
        $this->assertEquals(220000, $catalogProduct->pos_price);
        $this->assertEquals(150000, $catalogProduct->purchase_price);
    }

    /**
     * 2. Test POS Event Promotion (Inclusions and Exclusions)
     */
    public function test_pos_event_promotion_inclusions_and_exclusions()
    {
        $categoryBaju = Category::create(['name' => 'Baju', 'slug' => 'baju']);
        $categoryEmas = Category::create(['name' => 'Emas', 'slug' => 'emas']);

        $productBaju = Product::create([
            'name' => 'Baju Kaos',
            'slug' => 'baju-kaos',
            'price' => 100000,
            'category_id' => $categoryBaju->id,
            'is_active' => true,
        ]);

        $productEmas = Product::create([
            'name' => 'Cincin Emas',
            'slug' => 'cincin-emas',
            'price' => 500000,
            'category_id' => $categoryEmas->id,
            'is_active' => true,
        ]);

        // Event Promo: 20% discount for all items EXCEPT Emas category
        $promo = PosEventPromotion::create([
            'name' => 'Bazaar Sale 20%',
            'discount_type' => 'percent',
            'discount_amount' => 20,
            'applies_to' => 'all_items',
            'excluded_category_ids' => [$categoryEmas->id],
            'is_active' => true,
        ]);

        $this->assertTrue($promo->isProductEligible($productBaju->id, $productBaju->category_id));
        $this->assertFalse($promo->isProductEligible($productEmas->id, $productEmas->category_id));
    }

    /**
     * 3. Test Store-Wide Shared Hold Cart
     */
    public function test_store_wide_shared_hold_cart()
    {
        $cashierA = User::factory()->create(['name' => 'Kasir A']);

        $holdData = [
            'hold_id' => 'HOLD-TEST-001',
            'cashier_name' => $cashierA->name,
            'user_id' => $cashierA->id,
            'customer_name' => 'Pak Ahmad',
            'customer_phone' => '08123456789',
            'cart_data' => [
                ['name' => 'Barang A', 'price' => 50000, 'quantity' => 2],
            ],
            'total' => 100000,
        ];

        $heldCart = PosHeldCart::create($holdData);

        $this->assertDatabaseHas('pos_held_carts', [
            'hold_id' => 'HOLD-TEST-001',
            'customer_name' => 'Pak Ahmad',
        ]);

        // Verify another cashier (Kasir B) can access the store-wide hold cart
        $sharedCarts = PosHeldCart::latest()->get();
        $this->assertCount(1, $sharedCarts);
        $this->assertEquals('Pak Ahmad', $sharedCarts->first()->customer_name);
    }

    /**
     * 4. Test Kasbon Debt Transaction (Kasir A) & Next Shift Debt Collection (Kasir B)
     */
    public function test_kasbon_debt_checkout_and_next_shift_debt_collection()
    {
        Mail::fake();

        $kasirA = User::factory()->create(['name' => 'Kasir A']);
        $sessionA = PosSession::create([
            'cashier_id' => $kasirA->id,
            'opened_at' => now(),
            'opening_cash' => 100000,
            'status' => 'open',
        ]);

        $product = Product::create([
            'name' => 'Barang Toko',
            'slug' => 'barang-toko',
            'price' => 500000,
            'stock' => 10,
            'is_active' => true,
        ]);

        $service = new PosTransactionService();

        // 1. Kasir A processes Kasbon transaction (Customer: Pak Ahmad)
        $kasbonPayload = [
            'cashier_id' => $kasirA->id,
            'pos_session_id' => $sessionA->id,
            'payment_method' => 'kasbon',
            'cash_paid' => 0,
            'customer_name' => 'Pak Ahmad',
            'customer_phone' => '08123456789',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                ],
            ],
        ];

        $order = $service->completePosTransaction($kasbonPayload);

        // Verify Order status & due amount
        $this->assertEquals('unpaid', $order->payment_status);
        $this->assertTrue($order->is_kasbon);
        $this->assertEquals(500000, $order->due_amount);

        // Verify Stock IS decremented
        $this->assertEquals(9, $product->fresh()->stock);

        // Verify Cashflow cash-in for Kasir A drawer IS ZERO
        $cashflowA = Cashflow::where('order_id', $order->id)->first();
        $this->assertEquals('in', $cashflowA->type);
        $this->assertEquals('pos_sale_kasbon', $cashflowA->category);
        $this->assertEquals(0, $cashflowA->amount);

        // Verify Customer Total Debt
        $customer = PosCustomer::where('phone', '08123456789')->first();
        $this->assertNotNull($customer);
        $this->assertEquals(500000, $customer->total_debt);

        // 2. Next Shift: Kasir B collects debt payment from Pak Ahmad
        $kasirB = User::factory()->create(['name' => 'Kasir B']);
        $sessionB = PosSession::create([
            'cashier_id' => $kasirB->id,
            'opened_at' => now(),
            'opening_cash' => 200000,
            'status' => 'open',
        ]);

        $paymentPayload = [
            'order_id' => $order->id,
            'amount_paid' => 500000,
            'payment_method' => 'cash',
            'user_id' => $kasirB->id,
            'pos_session_id' => $sessionB->id,
            'notes' => 'Pelunasan Tunai Pak Ahmad di Kasir B',
        ];

        $debtPayment = $service->processDebtPayment($paymentPayload);

        // Verify Order is updated to Paid
        $this->assertEquals('paid', $order->fresh()->payment_status);
        $this->assertEquals(0, $order->fresh()->due_amount);
        $this->assertEquals(0, $customer->fresh()->total_debt);

        // Verify Stock is UNCHANGED during debt payment (remains 9)
        $this->assertEquals(9, $product->fresh()->stock);

        // Verify Cashflow FOR KASIR B DRAWER receives +Rp 500,000
        $cashflowB = Cashflow::where('category', 'pos_debt_payment')->first();
        $this->assertNotNull($cashflowB);
        $this->assertEquals('in', $cashflowB->type);
        $this->assertEquals(500000, $cashflowB->amount);
    }
}
