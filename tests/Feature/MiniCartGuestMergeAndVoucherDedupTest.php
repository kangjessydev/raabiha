<?php

namespace Tests\Feature;

use App\Livewire\Account;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\MiniCart;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Voucher;
use App\Services\VoucherService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MiniCartGuestMergeAndVoucherDedupTest extends TestCase
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
            'name' => 'Gamis Promo',
            'slug' => 'gamis-promo-'.uniqid(),
            'price' => 200000,
            'discount_price' => 150000,
            'stock' => 3,
            'is_active' => true,
            'has_variants' => false,
            'channel_visibility' => 'online_only',
        ], $overrides));
    }

    public function test_minicart_blocks_idor_enforces_stock_uses_effective_price_and_resets_buy_now(): void
    {
        $owner = User::factory()->create();
        $attacker = User::factory()->create();
        $product = $this->createProduct(['price' => 200000, 'discount_price' => 150000, 'stock' => 2]);

        $ownerCart = Cart::create(['user_id' => $owner->id, 'is_buy_now' => false]);
        $ownerItem = CartItem::create([
            'cart_id' => $ownerCart->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        // 1. Attacker cannot modify or delete owner's CartItem (IDOR blocked)
        Livewire::actingAs($attacker)
            ->test(MiniCart::class)
            ->call('incrementQuantity', $ownerItem->id)
            ->call('removeItem', $ownerItem->id);

        $this->assertDatabaseHas('cart_items', ['id' => $ownerItem->id, 'quantity' => 1]);

        // 2. Owner can increment up to stock=2, not beyond, and subtotal uses effective_price (150k)
        session(['checkout_is_buy_now' => true]);

        $component = Livewire::actingAs($owner)
            ->test(MiniCart::class)
            ->call('incrementQuantity', $ownerItem->id)
            ->call('incrementQuantity', $ownerItem->id); // Should be blocked at stock=2

        $this->assertSame(2, $ownerItem->fresh()->quantity);
        $component->assertViewHas('subtotal', 300000.0);

        // 3. proceedToCheckout resets checkout_is_buy_now to false
        $component->call('proceedToCheckout')->assertRedirect('/checkout');
        $this->assertFalse((bool) session('checkout_is_buy_now'));
    }

    public function test_guest_cart_is_merged_into_user_cart_on_login_and_register(): void
    {
        $product = $this->createProduct(['stock' => 5]);

        // 1. Login merge
        $user = User::factory()->create([
            'email' => 'pembeli@raabiha.test',
            'password' => Hash::make('secret123'),
        ]);

        session()->start();
        $guestSessionId = session()->getId();

        $guestCart = Cart::create([
            'session_id' => $guestSessionId,
            'user_id' => null,
            'is_buy_now' => false,
        ]);
        CartItem::create([
            'cart_id' => $guestCart->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        Livewire::test(Login::class)
            ->set('loginInput', 'pembeli@raabiha.test')
            ->set('password', 'secret123')
            ->call('login');

        $userCart = Cart::where('user_id', $user->id)->where('is_buy_now', false)->first();
        $this->assertNotNull($userCart);
        $this->assertSame(2, (int) $userCart->items()->where('product_id', $product->id)->first()?->quantity);
        $this->assertDatabaseMissing('carts', ['id' => $guestCart->id]);

        // 2. Register merge
        auth()->logout();
        session()->invalidate();
        session()->regenerateToken();
        $guestSessionId2 = session()->getId();

        $guestCart2 = Cart::create([
            'session_id' => $guestSessionId2,
            'user_id' => null,
            'is_buy_now' => false,
        ]);
        CartItem::create([
            'cart_id' => $guestCart2->id,
            'product_id' => $product->id,
            'quantity' => 3,
        ]);

        Livewire::test(Register::class)
            ->set('name', 'Pembeli Baru')
            ->set('email', 'baru@raabiha.test')
            ->set('password', 'password123')
            ->set('agree_terms', true)
            ->call('register');

        $newUser = User::where('email', 'baru@raabiha.test')->firstOrFail();
        $newUserCart = Cart::where('user_id', $newUser->id)->where('is_buy_now', false)->first();
        $this->assertNotNull($newUserCart);
        $this->assertSame(3, (int) $newUserCart->items()->where('product_id', $product->id)->first()?->quantity);
    }

    public function test_account_cancel_order_refuses_already_paid_order(): void
    {
        $user = User::factory()->create();
        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => 'ORD-PAID-ACC-1',
            'source' => 'ecommerce',
            'status' => 'pending',
            'payment_status' => 'paid',
            'subtotal' => 150000,
            'shipping_cost' => 10000,
            'discount_total' => 0,
            'grand_total' => 160000,
            'payment_method' => 'QRIS',
        ]);

        Livewire::actingAs($user)
            ->test(Account::class)
            ->set('cancelOrderId', $order->id)
            ->call('cancelOrder');

        $order->refresh();
        $this->assertSame('pending', $order->status);
        $this->assertSame('paid', $order->payment_status);
    }

    public function test_voucher_service_deduplicates_identical_voucher_codes(): void
    {
        Voucher::create([
            'code' => 'HEMAT20',
            'name' => 'Diskon 20rb',
            'discount_type' => 'fixed',
            'discount_amount' => 20000,
            'min_purchase' => 50000,
            'is_active' => true,
            'usable_channel' => 'online_only',
        ]);

        $result = app(VoucherService::class)->verifyAndCalculate(
            [
                ['code' => 'HEMAT20'],
                ['code' => 'hemat20'], // Duplicate code
            ],
            subtotal: 100000,
            shippingCost: 15000,
            itemCount: 1
        );

        $this->assertCount(1, $result['verified_vouchers']);
        $this->assertEquals(20000.0, (float) $result['product_discount']);
    }
}
