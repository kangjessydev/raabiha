<?php

namespace Tests\Feature;

use App\Livewire\Checkout;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ShippingMethod;
use App\Models\SiteSetting;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EcommerceCheckoutAndWebhookHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
    }

    private function createProduct(array $overrides = []): Product
    {
        return Product::create(array_merge([
            'name' => 'Gamis Test',
            'slug' => 'gamis-test-'.uniqid(),
            'price' => 150000,
            'stock' => 10,
            'weight' => 500,
            'is_active' => true,
            'has_variants' => false,
            'channel_visibility' => 'online_only',
            'is_custom' => false,
        ], $overrides));
    }

    private function seedManualShippingMethod(): void
    {
        ShippingMethod::create([
            'name' => 'JNE Reguler',
            'code' => 'jne',
            'is_active' => true,
            'config' => [
                'manual_rate_per_kg' => 20000,
            ],
        ]);

        SiteSetting::updateOrCreate(
            ['key' => 'manual_shipping_rules'],
            ['value' => json_encode([
                [
                    'courier' => 'jne',
                    'scope' => 'national',
                    'rate' => 20000,
                    'name' => 'Reguler',
                ],
            ])]
        );
    }

    public function test_shipping_rates_property_is_locked_against_client_tampering(): void
    {
        $this->expectException(CannotUpdateLockedPropertyException::class);

        Livewire::test(Checkout::class)
            ->set('shippingRates', [
                ['id' => 'jne|REG|100', 'price' => 100],
            ]);
    }

    public function test_clear_destination_resets_locked_shipping_cost_safely(): void
    {
        $component = Livewire::test(Checkout::class)
            ->set('selectedDestinationId', '12345')
            ->set('selectedDestinationLabel', 'Bandung, Jawa Barat')
            ->call('clearDestination');

        $component
            ->assertSet('selectedDestinationId', '')
            ->assertSet('selectedDestinationLabel', '')
            ->assertSet('shipping_cost', 0)
            ->assertSet('shipping_method', '');
    }

    public function test_checkout_rejects_forged_tunai_when_only_online_payment_methods_are_active(): void
    {
        $user = User::factory()->create();
        $product = $this->createProduct();
        $this->seedManualShippingMethod();

        PaymentMethod::create([
            'name' => 'QRIS',
            'code' => 'QRIS',
            'is_active' => true,
            'config' => ['availability' => 'online'],
        ]);

        $cart = Cart::create(['user_id' => $user->id, 'is_buy_now' => false]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        Livewire::actingAs($user)
            ->test(Checkout::class)
            ->call('switchToManualMode')
            ->set('email', $user->email)
            ->set('phone', '08123456789')
            ->set('first_name', 'Siti')
            ->set('last_name', 'Aminah')
            ->set('address', 'Jl. Merdeka 1')
            ->set('village', 'Citarum')
            ->set('province', 'Jawa Barat')
            ->set('city', 'Bandung')
            ->set('district', 'Bandung Wetan')
            ->set('selectedDestinationId', 'manual-1')
            ->call('generateShippingRates')
            ->set('agree_terms', true)
            ->set('payment_method', 'tunai')
            ->call('processCheckout')
            ->assertHasErrors(['payment_method']);

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_checkout_rejects_inactive_variant_inside_stock_lock(): void
    {
        $user = User::factory()->create();
        $product = $this->createProduct(['has_variants' => true, 'stock' => 0]);
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'name' => 'Sage - M',
            'sku' => 'SKU-SAGE-M',
            'price' => 150000,
            'stock' => 5,
            'is_active' => false,
            'channel_visibility' => 'online_only',
        ]);
        $this->seedManualShippingMethod();

        $cart = Cart::create(['user_id' => $user->id, 'is_buy_now' => false]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        Livewire::actingAs($user)
            ->test(Checkout::class)
            ->call('switchToManualMode')
            ->set('email', $user->email)
            ->set('phone', '08123456789')
            ->set('first_name', 'Siti')
            ->set('last_name', 'Aminah')
            ->set('address', 'Jl. Merdeka 1')
            ->set('village', 'Citarum')
            ->set('province', 'Jawa Barat')
            ->set('city', 'Bandung')
            ->set('district', 'Bandung Wetan')
            ->set('selectedDestinationId', 'manual-1')
            ->call('generateShippingRates')
            ->set('agree_terms', true)
            ->set('payment_method', 'tunai')
            ->call('processCheckout');

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_tripay_order_items_sum_matches_amount_when_voucher_discount_applied(): void
    {
        $user = User::factory()->create();
        $product = $this->createProduct(['price' => 100000]);
        $this->seedManualShippingMethod();

        PaymentMethod::create([
            'name' => 'QRIS',
            'code' => 'QRIS',
            'is_active' => true,
            'config' => ['availability' => 'online'],
        ]);

        SiteSetting::updateOrCreate(['key' => 'active_payment_gateway'], ['value' => 'tripay']);
        SiteSetting::updateOrCreate(['key' => 'tripay_api_key'], ['value' => 'test-api-key']);
        SiteSetting::updateOrCreate(['key' => 'tripay_private_key'], ['value' => 'test-private-key']);
        SiteSetting::updateOrCreate(['key' => 'tripay_merchant_code'], ['value' => 'T12345']);
        SiteSetting::updateOrCreate(['key' => 'tripay_mode'], ['value' => 'sandbox']);

        Voucher::create([
            'code' => 'HEMAT25',
            'name' => 'Diskon 25rb',
            'discount_type' => 'fixed',
            'discount_amount' => 25000,
            'min_purchase' => 50000,
            'is_active' => true,
            'usable_channel' => 'online_only',
        ]);

        $cart = Cart::create(['user_id' => $user->id, 'is_buy_now' => false]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $capturedPayload = null;
        Http::fake([
            'https://tripay.co.id/*' => function ($request) use (&$capturedPayload) {
                $capturedPayload = $request->data();

                return Http::response([
                    'success' => true,
                    'data' => [
                        'reference' => 'DEV-T12345-REF',
                        'checkout_url' => 'https://tripay.co.id/checkout/DEV-T12345-REF',
                    ],
                ], 200);
            },
        ]);

        Livewire::actingAs($user)
            ->test(Checkout::class)
            ->call('switchToManualMode')
            ->set('voucherCode', 'HEMAT25')
            ->call('applyVoucher')
            ->set('email', $user->email)
            ->set('phone', '08123456789')
            ->set('first_name', 'Siti')
            ->set('last_name', 'Aminah')
            ->set('address', 'Jl. Merdeka 1')
            ->set('village', 'Citarum')
            ->set('province', 'Jawa Barat')
            ->set('city', 'Bandung')
            ->set('district', 'Bandung Wetan')
            ->set('selectedDestinationId', 'manual-1')
            ->call('generateShippingRates')
            ->set('agree_terms', true)
            ->set('payment_method', 'QRIS')
            ->call('processCheckout');

        $this->assertNotNull($capturedPayload, 'Tripay API request should have been sent.');
        $itemsSum = array_reduce(
            $capturedPayload['order_items'] ?? [],
            fn (int $carry, array $item): int => $carry + ((int) $item['price'] * (int) $item['quantity']),
            0
        );
        $this->assertSame((int) $capturedPayload['amount'], $itemsSum);
    }

    public function test_tripay_and_xendit_expired_webhooks_do_not_overwrite_paid_orders(): void
    {
        $user = User::factory()->create();
        $product = $this->createProduct(['stock' => 5]);

        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => 'ORD-PAID-001',
            'source' => 'ecommerce',
            'status' => 'packed',
            'payment_status' => 'paid',
            'subtotal' => 150000,
            'shipping_cost' => 20000,
            'discount_total' => 0,
            'grand_total' => 170000,
            'payment_method' => 'QRIS',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'name' => $product->name,
            'price' => 150000,
            'quantity' => 2,
            'total' => 300000,
        ]);

        SiteSetting::updateOrCreate(['key' => 'tripay_private_key'], ['value' => 'tripay-secret']);
        SiteSetting::updateOrCreate(['key' => 'xendit_webhook_token'], ['value' => 'xendit-token']);

        // 1. Tripay EXPIRED webhook on already-paid order
        $tripayBody = json_encode([
            'merchant_ref' => $order->order_number,
            'reference' => 'TRIPAY-REF-1',
            'status' => 'EXPIRED',
        ]);
        $tripaySig = hash_hmac('sha256', $tripayBody, 'tripay-secret');

        $this->call('POST', '/webhook/tripay', [], [], [], [
            'HTTP_X_CALLBACK_SIGNATURE' => $tripaySig,
            'HTTP_X_CALLBACK_EVENT' => 'payment_status',
            'CONTENT_TYPE' => 'application/json',
        ], $tripayBody)->assertStatus(200);

        $order->refresh();
        $product->refresh();
        $this->assertSame('paid', $order->payment_status);
        $this->assertSame('packed', $order->status);
        $this->assertSame(5, $product->stock);

        // 2. Xendit EXPIRED webhook on already-paid order
        $this->withHeaders(['x-callback-token' => 'xendit-token'])
            ->postJson('/webhook/xendit', [
                'external_id' => $order->order_number,
                'id' => 'XENDIT-INV-1',
                'status' => 'EXPIRED',
            ])
            ->assertStatus(200);

        $order->refresh();
        $product->refresh();
        $this->assertSame('paid', $order->payment_status);
        $this->assertSame('packed', $order->status);
        $this->assertSame(5, $product->stock);
    }

    public function test_late_paid_webhook_on_cancelled_order_flags_manual_review_without_marking_packed(): void
    {
        $user = User::factory()->create();
        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => 'ORD-CANCEL-001',
            'source' => 'ecommerce',
            'status' => 'cancelled',
            'payment_status' => 'failed',
            'subtotal' => 150000,
            'shipping_cost' => 20000,
            'discount_total' => 0,
            'grand_total' => 170000,
            'payment_method' => 'QRIS',
            'is_stock_restored' => true,
        ]);

        SiteSetting::updateOrCreate(['key' => 'xendit_webhook_token'], ['value' => 'xendit-token']);

        $this->withHeaders(['x-callback-token' => 'xendit-token'])
            ->postJson('/webhook/xendit', [
                'external_id' => $order->order_number,
                'id' => 'XENDIT-INV-LATE',
                'status' => 'PAID',
            ])
            ->assertStatus(200);

        $order->refresh();
        $this->assertSame('cancelled', $order->status);
        $this->assertStringContainsString('verifikasi manual admin', (string) $order->notes);
    }
}
