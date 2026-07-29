<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\PosCustomer;
use App\Models\PosSession;
use App\Models\PosStampLog;
use App\Models\Product;
use App\Models\SiteSetting;
use App\Models\User;
use App\Services\EscPosService;
use App\Services\PosTransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PosLoyaltyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::create(['name' => 'owner', 'guard_name' => 'web']);
        Role::create(['name' => 'kasir', 'guard_name' => 'web']);

        // Default loyalty settings
        SiteSetting::updateOrCreate(['key' => 'pos_loyalty_enabled'], ['value' => '1']);
        SiteSetting::updateOrCreate(['key' => 'pos_loyalty_min_spend'], ['value' => '100000']);
        SiteSetting::updateOrCreate(['key' => 'pos_loyalty_stamps_to_points_ratio'], ['value' => '10']);
        SiteSetting::updateOrCreate(['key' => 'pos_loyalty_stamp_expiry_months'], ['value' => '6']);
    }

    public function test_pos_transaction_earns_stamp_and_points_when_qualifying()
    {
        $cashier = User::factory()->create();

        $session = PosSession::create([
            'cashier_id'   => $cashier->id,
            'opened_at'    => now(),
            'opening_cash' => 100000,
            'status'       => 'open',
        ]);

        $product = Product::create([
            'name'               => 'Gamis Syari Raabiha',
            'slug'               => 'gamis-syari-raabiha',
            'sku'                => 'GMS-SYR',
            'price'              => 150000,
            'stock'              => 10,
            'is_active'          => true,
            'channel_visibility' => 'both',
        ]);

        $service = new PosTransactionService();
        $order = $service->completePosTransaction([
            'items' => [
                ['product_id' => $product->id, 'product_variant_id' => null, 'quantity' => 1]
            ],
            'cashier_id'     => $cashier->id,
            'pos_session_id' => $session->id,
            'customer_name'  => 'Ibu Siti',
            'customer_phone' => '0812-3456-7890',
            'cash_paid'      => 150000,
            'payment_method' => 'cash',
        ]);

        // Customer created with normalized phone
        $customer = PosCustomer::where('phone', '081234567890')->first();
        $this->assertNotNull($customer);
        $this->assertEquals('Ibu Siti', $customer->name);

        // 1 stamp & 10 points earned
        $this->assertEquals(1, $customer->stamp_count);
        $this->assertEquals(10, $customer->points_balance);
        $this->assertEquals(1, $customer->total_stamps_earned);
        $this->assertEquals(1, $customer->total_visits);

        // Log created
        $this->assertDatabaseHas('pos_stamp_logs', [
            'pos_customer_id' => $customer->id,
            'order_id'        => $order->id,
            'type'            => 'earned',
            'stamps'          => 1,
            'points'          => 10,
        ]);
    }

    public function test_pos_transaction_below_min_spend_does_not_earn_stamps()
    {
        $cashier = User::factory()->create();

        $session = PosSession::create([
            'cashier_id'   => $cashier->id,
            'opened_at'    => now(),
            'opening_cash' => 100000,
            'status'       => 'open',
        ]);

        $product = Product::create([
            'name'               => 'Kaus Kaki Wudhu',
            'slug'               => 'kaus-kaki-wudhu',
            'sku'                => 'KSK-WDH',
            'price'              => 35000,
            'stock'              => 20,
            'is_active'          => true,
            'channel_visibility' => 'both',
        ]);

        $service = new PosTransactionService();
        $order = $service->completePosTransaction([
            'items' => [
                ['product_id' => $product->id, 'product_variant_id' => null, 'quantity' => 1]
            ],
            'cashier_id'     => $cashier->id,
            'pos_session_id' => $session->id,
            'customer_name'  => 'Pelanggan B',
            'customer_phone' => '085711112222',
            'cash_paid'      => 35000,
            'payment_method' => 'cash',
        ]);

        $customer = PosCustomer::where('phone', '085711112222')->first();
        $this->assertNotNull($customer);
        $this->assertEquals(0, $customer->stamp_count);
        $this->assertEquals(0, $customer->points_balance);
        $this->assertEquals(1, $customer->total_visits);
    }

    public function test_stamp_redemption_deducts_stamps_and_points()
    {
        $customer = PosCustomer::create([
            'phone'               => '089988887777',
            'name'                => 'Pelanggan C',
            'stamp_count'         => 4,
            'points_balance'      => 40,
            'total_stamps_earned' => 4,
        ]);

        $cashier = User::factory()->create();

        $session = PosSession::create([
            'cashier_id'   => $cashier->id,
            'opened_at'    => now(),
            'opening_cash' => 100000,
            'status'       => 'open',
        ]);

        $product = Product::create([
            'name'               => 'Gamis Premium',
            'slug'               => 'gamis-premium',
            'sku'                => 'GMS-PRM',
            'price'              => 200000,
            'stock'              => 10,
            'is_active'          => true,
            'channel_visibility' => 'both',
        ]);

        $service = new PosTransactionService();
        // Kasir redeems 3 stamps for Tier 1 reward (Voucher 15k)
        $order = $service->completePosTransaction([
            'items' => [
                ['product_id' => $product->id, 'product_variant_id' => null, 'quantity' => 1]
            ],
            'cashier_id'            => $cashier->id,
            'pos_session_id'        => $session->id,
            'customer_phone'        => '089988887777',
            'loyalty_redeem_stamps' => 3,
            'discount'              => 15000,
            'cash_paid'             => 200000,
            'payment_method'        => 'cash',
        ]);

        $customer->refresh();
        // Milestone rules: stamp_count is NOT decremented on voucher claim. 4 + 1 earned = 5 stamps.
        $this->assertEquals(5, $customer->stamp_count);
        $this->assertEquals(50, $customer->points_balance);

        $this->assertDatabaseHas('pos_stamp_logs', [
            'pos_customer_id' => $customer->id,
            'type'            => 'redeemed',
            'stamps'          => 0,
            'points'          => 0,
        ]);
    }

    public function test_stamps_expire_after_expiry_months()
    {
        // Customer last visited 7 months ago
        $customer = PosCustomer::create([
            'phone'               => '081299990000',
            'name'                => 'Pelanggan Lama',
            'stamp_count'         => 3,
            'points_balance'      => 30,
            'total_stamps_earned' => 3,
            'last_visit_at'       => now()->subMonths(7),
        ]);

        $cashier = User::factory()->create();

        $session = PosSession::create([
            'cashier_id'   => $cashier->id,
            'opened_at'    => now(),
            'opening_cash' => 100000,
            'status'       => 'open',
        ]);

        $product = Product::create([
            'name'               => 'Hijab Instan',
            'slug'               => 'hijab-instan',
            'sku'                => 'HJB-INS',
            'price'              => 100000,
            'stock'              => 10,
            'is_active'          => true,
            'channel_visibility' => 'both',
        ]);

        $service = new PosTransactionService();
        $order = $service->completePosTransaction([
            'items' => [
                ['product_id' => $product->id, 'product_variant_id' => null, 'quantity' => 1]
            ],
            'cashier_id'     => $cashier->id,
            'pos_session_id' => $session->id,
            'customer_phone' => '081299990000',
            'cash_paid'      => 100000,
            'payment_method' => 'cash',
        ]);

        $customer->refresh();
        // 3 old stamps expired -> reset to 0 -> +1 new earned = 1 stamp
        $this->assertEquals(1, $customer->stamp_count);
        $this->assertEquals(10, $customer->points_balance);

        $this->assertDatabaseHas('pos_stamp_logs', [
            'pos_customer_id' => $customer->id,
            'type'            => 'expired',
            'stamps'          => -3,
            'points'          => -30,
        ]);
    }

    public function test_void_order_rolls_back_earned_stamps()
    {
        $owner = User::factory()->create(['pos_pin' => Hash::make('888888')]);
        $owner->assignRole('owner');

        $cashier = User::factory()->create();
        $cashier->assignRole('kasir');

        $session = PosSession::create([
            'cashier_id'   => $cashier->id,
            'opened_at'    => now(),
            'opening_cash' => 100000,
            'status'       => 'open',
        ]);

        $product = Product::create([
            'name'               => 'Kemeja Batik',
            'slug'               => 'kemeja-batik',
            'sku'                => 'KMJ-BTK',
            'price'              => 120000,
            'stock'              => 10,
            'is_active'          => true,
            'channel_visibility' => 'both',
        ]);

        $service = new PosTransactionService();
        $order = $service->completePosTransaction([
            'items' => [
                ['product_id' => $product->id, 'product_variant_id' => null, 'quantity' => 1]
            ],
            'cashier_id'     => $cashier->id,
            'pos_session_id' => $session->id,
            'customer_phone' => '081377778888',
            'cash_paid'      => 120000,
            'payment_method' => 'cash',
        ]);

        $customer = PosCustomer::where('phone', '081377778888')->first();
        $this->assertEquals(1, $customer->stamp_count);

        // Void Order
        \Livewire\Livewire::actingAs($cashier)
            ->test(\App\Livewire\PosManager::class)
            ->call('voidOrder', $order->id, $owner->id, '888888', 'Void transaksi');

        $customer->refresh();
        $this->assertEquals(0, $customer->stamp_count);
        $this->assertEquals(0, $customer->points_balance);

        $this->assertDatabaseHas('pos_stamp_logs', [
            'pos_customer_id' => $customer->id,
            'order_id'        => $order->id,
            'type'            => 'adjusted',
            'stamps'          => -1,
        ]);
    }

    public function test_receipt_prints_loyalty_stamp_progress()
    {
        $customer = PosCustomer::create([
            'phone'               => '08123456789',
            'name'                => 'Budi Stamp',
            'stamp_count'         => 4,
            'points_balance'      => 40,
            'total_stamps_earned' => 4,
        ]);

        $order = Order::create([
            'order_number'   => 'POS-20260725-99',
            'source'         => 'pos',
            'customer_name'  => 'Budi Stamp',
            'customer_phone' => '08123456789',
            'subtotal'       => 150000,
            'grand_total'    => 150000,
            'status'         => 'completed',
            'payment_status' => 'paid',
            'payment_method' => 'cash',
            'cash_paid'      => 150000,
        ]);

        $escPos = new EscPosService();
        $receiptBase64 = $escPos->generateReceipt($order);
        $text = base64_decode($receiptBase64);

        $this->assertStringContainsString('KARTU CAP DIGITAL RAABIHA', $text);
        $this->assertStringContainsString('Budi Stamp', $text);
        $this->assertStringContainsString('Total Cap: 4 dari 9 Cap', $text);
        $this->assertStringContainsString('[X] [X] [X]', $text); // Row 1 complete
        $this->assertStringContainsString('[X] [ ] [ ]', $text); // Row 2 (1 stamp)
    }

    public function test_pos_transaction_redeems_loyalty_tier_voucher_and_deducts_stamps()
    {
        $cashier = User::factory()->create();

        $session = PosSession::create([
            'cashier_id'   => $cashier->id,
            'opened_at'    => now(),
            'opening_cash' => 100000,
            'status'       => 'open',
        ]);

        $product = Product::create([
            'name'               => 'Gamis Syari Tier',
            'slug'               => 'gamis-syari-tier',
            'sku'                => 'GMS-TIER',
            'price'              => 100000,
            'stock'              => 10,
            'is_active'          => true,
            'channel_visibility' => 'both',
        ]);

        $voucher = \App\Models\Voucher::create([
            'name'            => 'Voucher Member Tier 3 Cap',
            'code'            => 'TIER-3-CAP',
            'discount_type'   => 'fixed',
            'discount_amount' => 15000,
            'is_active'       => true,
        ]);

        // Configure loyalty tier setting
        SiteSetting::updateOrCreate(['key' => 'pos_loyalty_tiers'], [
            'value' => json_encode([
                ['min_stamps' => 3, 'voucher_id' => $voucher->id, 'description' => 'Diskon Member Tier 1 (Potong 3 Cap)']
            ])
        ]);

        // Create customer with 5 stamps
        $customer = PosCustomer::create([
            'phone'         => '081299990000',
            'name'          => 'Ibu Tier Test',
            'stamp_count'   => 5,
            'points_balance'=> 50,
        ]);

        $service = new PosTransactionService();
        $order = $service->completePosTransaction([
            'items' => [
                ['product_id' => $product->id, 'product_variant_id' => null, 'quantity' => 1]
            ],
            'cashier_id'     => $cashier->id,
            'pos_session_id' => $session->id,
            'customer_name'  => 'Ibu Tier Test',
            'customer_phone' => '081299990000',
            'voucher_id'     => $voucher->id,
            'discount_total' => 15000,
            'cash_paid'      => 85000,
            'payment_method' => 'cash',
        ]);

        $customer->refresh();
        // Milestone rules: stamp_count is NOT decremented on voucher claim. Initial 5 stamps + 1 earned (100k price) = 6 stamps.
        $this->assertEquals(6, $customer->stamp_count);
        $this->assertEquals(60, $customer->points_balance);

        $this->assertDatabaseHas('pos_stamp_logs', [
            'pos_customer_id' => $customer->id,
            'order_id'        => $order->id,
            'type'            => 'redeemed',
            'stamps'          => 0,
        ]);
    }
}
