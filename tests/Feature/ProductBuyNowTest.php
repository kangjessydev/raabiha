<?php

namespace Tests\Feature;

use App\Livewire\Checkout;
use App\Livewire\ProductDetail;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProductBuyNowTest extends TestCase
{
    use RefreshDatabase;

    public function test_buy_now_isolates_checkout_to_current_product_only(): void
    {
        // 1. Buat produk A (produk lama di keranjang) dan produk B (produk yang dibeli via buyNow)
        $productA = Product::create([
            'name' => 'Abaya Basic',
            'slug' => 'abaya-basic',
            'price' => 150000,
            'stock' => 5,
            'is_active' => true,
        ]);

        $productB = Product::create([
            'name' => 'Gamis Silk Premium',
            'slug' => 'gamis-silk-premium',
            'price' => 300000,
            'stock' => 10,
            'is_active' => true,
        ]);

        // Simulasikan produk A sudah ada di keranjang sebelumnya
        $cart = Cart::create([
            'session_id' => session()->getId(),
            'is_buy_now' => false,
        ]);

        $itemA = CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $productA->id,
            'quantity' => 1,
        ]);

        // 2. Akses halaman produk B dan klik tombol "Beli Sekarang" (buyNow)
        Livewire::test(ProductDetail::class, ['slug' => $productB->slug])
            ->call('buyNow')
            ->assertRedirect('/checkout');

        // Pastikan session checkout_item_ids telah diset dan HANYA berisi produk B
        $this->assertTrue(session()->has('checkout_item_ids'));
        $checkoutItemIds = session('checkout_item_ids');
        $this->assertCount(1, $checkoutItemIds);

        // ID di checkout harus berbeda dengan item A
        $this->assertNotContains((string) $itemA->id, $checkoutItemIds);

        // 3. Masuk ke komponen Checkout dan pastikan item yang diproses HANYA produk B
        $checkoutComponent = Livewire::test(Checkout::class);
        $items = $checkoutComponent->get('checkoutItems');
        $this->assertCount(1, $items);
        $this->assertEquals($productB->id, $items->first()->product_id);

        // Item A tetap ada di keranjang utama dan tidak terhapus
        $this->assertDatabaseHas('cart_items', [
            'id' => $itemA->id,
            'product_id' => $productA->id,
        ]);
    }

    public function test_buy_now_aborts_when_store_is_in_holiday_mode(): void
    {
        \App\Models\SiteSetting::create([
            'key' => 'store_holiday_mode',
            'value' => '1',
        ]);
        \App\Models\SiteSetting::create([
            'key' => 'store_holiday_message',
            'value' => 'Toko sedang libur lebaran.',
        ]);

        $product = Product::create([
            'name' => 'Tunik Polos',
            'slug' => 'tunik-polos',
            'price' => 120000,
            'stock' => 10,
            'is_active' => true,
        ]);

        $test = Livewire::test(ProductDetail::class, ['slug' => $product->slug]);
        $test->call('buyNow');
        $test->assertNoRedirect();
        $this->assertFalse(session()->has('checkout_item_ids'));
    }
}
