<?php

namespace Tests\Feature;

use App\Livewire\Cart as CartComponent;
use App\Livewire\Checkout;
use App\Livewire\ProductDetail;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BuyNowIsolationAndCartStockSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_buy_now_ignores_regular_cart_quantity_and_does_not_mutate_regular_cart(): void
    {
        $user = User::factory()->create();

        $product = Product::create([
            'name' => 'Gamis Eksklusif Stok 3',
            'slug' => 'gamis-eksklusif-stok-3',
            'price' => 250000,
            'stock' => 3,
            'has_variants' => false,
            'is_active' => true,
            'channel_visibility' => 'online_only',
        ]);

        // User sudah punya 3 pcs (penuh stok) di keranjang reguler
        $regularCart = Cart::create([
            'user_id' => $user->id,
            'is_buy_now' => false,
        ]);

        $regularItem = CartItem::create([
            'cart_id' => $regularCart->id,
            'product_id' => $product->id,
            'quantity' => 3,
        ]);

        // Klik Beli Sekarang qty 1 harus tetap berhasil (mengabaikan qty keranjang reguler)
        Livewire::actingAs($user)
            ->test(ProductDetail::class, ['slug' => $product->slug])
            ->set('quantity', 1)
            ->call('buyNow')
            ->assertRedirect('/checkout');

        $this->assertTrue((bool) session('checkout_is_buy_now'));

        // Keranjang reguler tetap utuh dengan qty 3
        $this->assertDatabaseHas('cart_items', [
            'id' => $regularItem->id,
            'cart_id' => $regularCart->id,
            'quantity' => 3,
        ]);

        // Cart buy_now terpisah memiliki tepat 1 item dengan qty 1
        $buyNowCart = Cart::where('user_id', $user->id)->where('is_buy_now', true)->first();
        $this->assertNotNull($buyNowCart);
        $this->assertCount(1, $buyNowCart->items);
        $this->assertEquals(1, $buyNowCart->items->first()->quantity);

        // Halaman Checkout hanya memuat item Buy Now (qty 1), bukan item keranjang reguler (qty 3)
        $checkoutComponent = Livewire::actingAs($user)->test(Checkout::class);
        $checkoutItems = $checkoutComponent->get('checkoutItems');
        $this->assertCount(1, $checkoutItems);
        $this->assertEquals($buyNowCart->items->first()->id, $checkoutItems->first()->id);
        $this->assertEquals(1, $checkoutItems->first()->quantity);
    }

    public function test_regular_cart_proceed_to_checkout_resets_buy_now_session_flag(): void
    {
        $user = User::factory()->create();

        $productA = Product::create([
            'name' => 'Produk Keranjang Reguler',
            'slug' => 'produk-keranjang-reguler',
            'price' => 150000,
            'stock' => 5,
            'has_variants' => false,
            'is_active' => true,
            'channel_visibility' => 'online_only',
        ]);

        $productB = Product::create([
            'name' => 'Produk Buy Now Lama',
            'slug' => 'produk-buy-now-lama',
            'price' => 300000,
            'stock' => 5,
            'has_variants' => false,
            'is_active' => true,
            'channel_visibility' => 'online_only',
        ]);

        $regularCart = Cart::create(['user_id' => $user->id, 'is_buy_now' => false]);
        $regularItem = CartItem::create([
            'cart_id' => $regularCart->id,
            'product_id' => $productA->id,
            'quantity' => 2,
        ]);

        $buyNowCart = Cart::create(['user_id' => $user->id, 'is_buy_now' => true]);
        $buyNowItem = CartItem::create([
            'cart_id' => $buyNowCart->id,
            'product_id' => $productB->id,
            'quantity' => 1,
        ]);

        // Simulasikan sesi Buy Now sebelumnya masih tersisa di session
        session([
            'checkout_is_buy_now' => true,
            'checkout_item_ids' => [(string) $buyNowItem->id],
        ]);

        // User checkout dari halaman /cart reguler
        Livewire::actingAs($user)
            ->test(CartComponent::class)
            ->call('proceedToCheckout')
            ->assertRedirect('/checkout');

        $this->assertFalse((bool) session('checkout_is_buy_now'));
        $this->assertEquals([(string) $regularItem->id], session('checkout_item_ids'));

        // Checkout harus menampilkan Produk A (reguler), bukan Produk B (Buy Now lama)
        $checkoutItems = Livewire::actingAs($user)->test(Checkout::class)->get('checkoutItems');
        $this->assertCount(1, $checkoutItems);
        $this->assertEquals($productA->id, $checkoutItems->first()->product_id);
        $this->assertEquals(2, $checkoutItems->first()->quantity);
    }

    public function test_cart_auto_clamps_quantity_and_records_notice_when_stock_drops(): void
    {
        $user = User::factory()->create();

        $product = Product::create([
            'name' => 'Khimar Ceruty',
            'slug' => 'khimar-ceruty',
            'price' => 120000,
            'stock' => 2, // Stok riil turun jadi 2
            'has_variants' => false,
            'is_active' => true,
            'channel_visibility' => 'online_only',
        ]);

        $cart = Cart::create(['user_id' => $user->id, 'is_buy_now' => false]);
        $cartItem = CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 5, // Sebelumnya user menyimpan 5 pcs
        ]);

        $component = Livewire::actingAs($user)->test(CartComponent::class);

        // Qty di DB otomatis di-clamp ke 2
        $this->assertDatabaseHas('cart_items', [
            'id' => $cartItem->id,
            'quantity' => 2,
        ]);

        $notices = $component->get('stockNotices');
        $this->assertNotEmpty($notices);
        $this->assertStringContainsString('otomatis disesuaikan dari 5 menjadi 2 pcs', $notices[0]);

        $availability = $component->get('itemAvailability');
        $this->assertTrue($availability[(string) $cartItem->id]['is_available']);
        $this->assertEquals(2, $availability[(string) $cartItem->id]['available_stock']);
    }

    public function test_cart_unchecks_and_blocks_checkout_for_out_of_stock_or_inactive_variant(): void
    {
        $user = User::factory()->create();

        $product = Product::create([
            'name' => 'Gamis Varian Non-Aktif',
            'slug' => 'gamis-varian-non-aktif',
            'price' => 200000,
            'stock' => 0,
            'has_variants' => true,
            'is_active' => true,
            'channel_visibility' => 'online_only',
        ]);

        $inactiveVariant = ProductVariant::create([
            'product_id' => $product->id,
            'name' => 'Maroon - M',
            'stock' => 5,
            'selling_price' => 200000,
            'is_active' => false,
        ]);

        $outOfStockProduct = Product::create([
            'name' => 'Pashmina Habis',
            'slug' => 'pashmina-habis',
            'price' => 75000,
            'stock' => 0,
            'has_variants' => false,
            'is_active' => true,
            'channel_visibility' => 'online_only',
        ]);

        $cart = Cart::create(['user_id' => $user->id, 'is_buy_now' => false]);
        $itemInactive = CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'product_variant_id' => $inactiveVariant->id,
            'quantity' => 1,
        ]);
        $itemOutOfStock = CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $outOfStockProduct->id,
            'quantity' => 1,
        ]);

        $component = Livewire::actingAs($user)->test(CartComponent::class);

        // Kedua item otomatis tidak tercentang dan tidak bisa di-checkout
        $this->assertEmpty($component->get('selectedItems'));
        $this->assertFalse($component->get('selectAll'));
        $this->assertCount(2, $component->get('stockNotices'));

        $availability = $component->get('itemAvailability');
        $this->assertFalse($availability[(string) $itemInactive->id]['is_available']);
        $this->assertFalse($availability[(string) $itemOutOfStock->id]['is_available']);

        // Paksa proceedToCheckout harus ditolak
        $component->call('proceedToCheckout')
            ->assertNoRedirect();
    }

    public function test_proceed_to_checkout_blocks_redirect_if_stock_drops_mid_session(): void
    {
        $user = User::factory()->create();

        $product = Product::create([
            'name' => 'Abaya Mid Session',
            'slug' => 'abaya-mid-session',
            'price' => 280000,
            'stock' => 5,
            'has_variants' => false,
            'is_active' => true,
            'channel_visibility' => 'online_only',
        ]);

        $cart = Cart::create(['user_id' => $user->id, 'is_buy_now' => false]);
        $cartItem = CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 4,
        ]);

        // Saat mount, stok masih 5 (aman)
        $component = Livewire::actingAs($user)->test(CartComponent::class);
        $this->assertEmpty($component->get('stockNotices'));

        // Sebelum klik checkout, stok di DB berkurang jadi 2 (misal dibeli orang lain)
        $product->update(['stock' => 2]);

        // Klik checkout pertama kali harus menahan redirect, auto-clamp qty ke 2, dan memberi tahu user
        $component->call('proceedToCheckout')
            ->assertNoRedirect();

        $this->assertDatabaseHas('cart_items', [
            'id' => $cartItem->id,
            'quantity' => 2,
        ]);
        $this->assertNotEmpty($component->get('stockNotices'));

        // Setelah user melihat perubahan dan klik checkout lagi (sekarang qty 2 <= stok 2), baru boleh lanjut
        $component->call('proceedToCheckout')
            ->assertRedirect('/checkout');
    }
}
