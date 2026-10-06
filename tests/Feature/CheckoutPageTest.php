<?php

namespace Tests\Feature;

use App\Livewire\Checkout;
use App\Models\ShippingMethod;
use App\Models\SiteSetting;
use Livewire\Livewire;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CheckoutPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_page_renders_successfully()
    {
        $response = $this->get('/checkout');
        // If not logged in, checkout redirects to login or cart, let's see what happens
        $response->assertStatus(200);
    }

    public function test_manual_shipping_calculates_rates_multiplied_by_weight_kg()
    {
        $courier = ShippingMethod::create([
            'name' => 'JNE',
            'code' => 'jne',
            'is_active' => true,
        ]);

        $component = Livewire::test(Checkout::class);

        // 2500 grams should ceil to 3 kg. Jawa default base rate = Rp 12,000 -> 36,000
        $rates = $component->instance()->getManualShippingRates(2500, collect([$courier]), 'JAWA TIMUR');

        $this->assertNotEmpty($rates);
        $this->assertEquals(36000, $rates[0]['price']);
        $this->assertEquals(36000, $rates[0]['discounted_price']);
    }

    public function test_auto_fallback_triggers_when_api_returns_empty_rates()
    {
        ShippingMethod::create([
            'name' => 'JNE',
            'code' => 'jne',
            'is_active' => true,
        ]);

        SiteSetting::create([
            'key' => 'active_shipping_provider',
            'value' => 'binderbyte',
        ]);

        $component = Livewire::test(Checkout::class)
            ->set('addressMode', 'api')
            ->set('selectedProvinceId', '32')
            ->set('province', 'JAWA BARAT')
            ->set('selectedDestinationId', '320414')
            ->set('selectedDestinationLabel', 'Bojongsoang, Bandung, JAWA BARAT')
            ->call('generateShippingRates');

        // It should safely fall back to store manual shipping rates
        $this->assertTrue($component->get('isFallbackShipping'));
        $this->assertNotEmpty($component->get('shippingRates'));
        $this->assertGreaterThan(0, $component->get('shipping_cost'));
    }

    public function test_process_checkout_fails_if_shipping_method_not_selected()
    {
        $component = Livewire::test(Checkout::class)
            ->set('first_name', 'Budi')
            ->set('last_name', 'Santoso')
            ->set('address', 'Jl. Merdeka No. 1')
            ->set('village', 'Sukamaju')
            ->set('selectedDestinationId', '320414')
            ->set('shipping_method', '')
            ->set('agree_terms', true)
            ->call('processCheckout');

        $component->assertHasErrors(['shipping_method']);
    }

    public function test_process_checkout_fails_if_shipping_rate_is_zero()
    {
        $component = Livewire::test(Checkout::class)
            ->set('first_name', 'Budi')
            ->set('last_name', 'Santoso')
            ->set('email', 'budi@example.com')
            ->set('address', 'Jl. Merdeka No. 1')
            ->set('village', 'Sukamaju')
            ->set('selectedDestinationId', '320414')
            ->set('shippingRates', [
                [
                    'id' => 'fake_courier',
                    'price' => 0,
                    'courier_name' => 'Fake Courier',
                    'service_name' => 'Fake Service',
                    'discounted_price' => 0,
                ]
            ])
            ->set('shipping_method', 'fake_courier')
            ->set('agree_terms', true)
            ->call('processCheckout');

        $component->assertHasErrors(['shipping_method']);
    }

    public function test_fail_order_and_restore_stock_restores_inventory()
    {
        foreach (['super_admin', 'owner', 'finance'] as $roleName) {
            \Spatie\Permission\Models\Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        }

        $product = \App\Models\Product::create([
            'name' => 'Gamis Syari Test',
            'slug' => 'gamis-syari-test',
            'stock' => 8,
            'price' => 150000,
            'is_active' => true,
        ]);

        $order = \App\Models\Order::create([
            'order_number' => 'ORD-TEST-001',
            'status' => 'pending',
            'payment_status' => 'pending',
            'subtotal' => 150000,
            'grand_total' => 170000,
            'shipping_cost' => 20000,
            'shipping_address' => [],
        ]);

        \App\Models\OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'name' => $product->name,
            'price' => 150000,
            'quantity' => 2,
            'total' => 300000,
        ]);

        $component = Livewire::test(Checkout::class);
        
        $reflection = new \ReflectionClass($component->instance());
        $method = $reflection->getMethod('failOrderAndRestoreStock');
        $method->setAccessible(true);
        $method->invokeArgs($component->instance(), [$order, 'Connection timeout to payment gateway']);

        $order->refresh();
        $product->refresh();

        $this->assertEquals('cancelled', $order->status);
        $this->assertEquals('failed', $order->payment_status);
        $this->assertStringContainsString('Connection timeout', $order->notes);
        $this->assertEquals(10, $product->stock); // Restored from 8 back to 10

        $log = \App\Models\StockLog::where('product_id', $product->id)->latest()->first();
        $this->assertNotNull($log);
        $this->assertEquals('in', $log->type);
        $this->assertEquals(2, $log->quantity_change);
        $this->assertEquals('Payment Failed', $log->reason);
    }
}
