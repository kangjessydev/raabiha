<?php

namespace Tests\Feature;

use App\Models\Cashflow;
use App\Models\Category;
use App\Models\Order;
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
}
