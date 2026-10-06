<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\SiteSetting;
use App\Models\Voucher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CheckoutVoucherSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        foreach (['super_admin', 'owner', 'finance'] as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        \App\Models\ShippingMethod::create([
            'name' => 'JNE',
            'code' => 'jne',
            'is_active' => true,
        ]);
    }

    public function test_sensitive_properties_are_locked_from_client_tampering(): void
    {
        $this->expectException(CannotUpdateLockedPropertyException::class);

        Livewire::test(\App\Livewire\Checkout::class)
            ->set('appliedVouchers', [['id' => 999, 'code' => 'FAKE', 'discount' => 100000]]);
    }

    public function test_shipping_cost_is_locked_from_client_tampering(): void
    {
        $this->expectException(CannotUpdateLockedPropertyException::class);

        Livewire::test(\App\Livewire\Checkout::class)
            ->set('shipping_cost', 0);
    }

    public function test_process_checkout_recalculates_discount_server_side_from_model(): void
    {
        $product = Product::create([
            'name' => 'Dress Premium',
            'slug' => 'dress-premium',
            'stock' => 10,
            'price' => 200000,
            'is_active' => true,
        ]);

        $voucher = Voucher::create([
            'code' => 'DISC10',
            'name' => 'Diskon 10 Persen',
            'discount_type' => 'percent',
            'discount_amount' => 10, // 10% dari 200.000 = 20.000
            'max_discount' => 20000,
            'is_active' => true,
            'used_count' => 0,
            'max_uses' => 100,
        ]);

        $sessionCart = Cart::create(['session_id' => session()->getId()]);
        CartItem::create([
            'cart_id' => $sessionCart->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        // Simulasikan session voucher valid
        session(['applied_vouchers' => [$voucher->toArray()]]);

        $component = Livewire::test(\App\Livewire\Checkout::class)
            ->set('first_name', 'Siti')
            ->set('last_name', 'Fatimah')
            ->set('email', 'siti@example.com')
            ->set('phone', '081234567890')
            ->set('address', 'Jl. Melati No. 12')
            ->set('village', 'Sukamaju')
            ->set('addressMode', 'manual')
            ->set('selectedDestinationId', '320414')
            ->set('selectedDestinationLabel', 'Coblong, Bandung, Jawa Barat')
            ->set('province', 'Jawa Barat')
            ->set('city', 'Bandung')
            ->set('district', 'Coblong')
            ->set('postal_code', '40132')
            ->set('payment_method', 'tunai')
            ->set('agree_terms', true);

        // Pilih metode pengiriman manual flat
        $component->call('generateShippingRates');
        $rates = $component->get('shippingRates');
        $this->assertNotEmpty($rates);
        $component->set('shipping_method', $rates[0]['id']);

        $component->call('processCheckout');

        $order = Order::latest()->first();
        $this->assertNotNull($order);
        // Server mereposisi discount_total ke 20.000 (10% dari 200.000)
        $this->assertEquals(20000, (float) $order->discount_total);
        $this->assertEquals(1, $voucher->fresh()->used_count);
    }

    public function test_process_checkout_fails_if_voucher_quota_exhausted(): void
    {
        $product = Product::create([
            'name' => 'Khimar Daily',
            'slug' => 'khimar-daily',
            'stock' => 10,
            'price' => 100000,
            'is_active' => true,
        ]);

        $voucher = Voucher::create([
            'code' => 'LIMITED1',
            'name' => 'Voucher 1 Pengguna',
            'discount_type' => 'fixed',
            'discount_amount' => 15000,
            'is_active' => true,
            'used_count' => 1,
            'max_uses' => 1, // Kuota sudah habis di DB
        ]);

        $sessionCart = Cart::create(['session_id' => session()->getId()]);
        CartItem::create([
            'cart_id' => $sessionCart->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        // Manipulasi session seolah-olah voucher masih ada
        session(['applied_vouchers' => [$voucher->toArray()]]);

        $component = Livewire::test(\App\Livewire\Checkout::class)
            ->set('first_name', 'Rina')
            ->set('last_name', 'Sari')
            ->set('email', 'rina@example.com')
            ->set('phone', '081299998888')
            ->set('address', 'Jl. Mawar No. 4')
            ->set('village', 'Cipaganti')
            ->set('addressMode', 'manual')
            ->set('selectedDestinationId', '320414')
            ->set('selectedDestinationLabel', 'Coblong, Bandung, Jawa Barat')
            ->set('province', 'Jawa Barat')
            ->set('city', 'Bandung')
            ->set('district', 'Coblong')
            ->set('postal_code', '40131')
            ->set('agree_terms', true);

        $component->call('generateShippingRates');
        $rates = $component->get('shippingRates');
        $this->assertNotEmpty($rates);
        $component->set('shipping_method', $rates[0]['id']);

        $component->call('processCheckout');

        // Order tidak boleh tercipta karena voucher ditolak server-side
        $this->assertEquals(0, Order::count());
    }
}
