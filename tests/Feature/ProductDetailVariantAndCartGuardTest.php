<?php

namespace Tests\Feature;

use App\Filament\Resources\Products\Pages\EditProduct;
use App\Filament\Resources\Products\RelationManagers\VariantsRelationManager;
use App\Livewire\Cart as CartComponent;
use App\Livewire\ProductDetail;
use App\Models\Attribute;
use App\Models\AttributeOption;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Media;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProductDetailVariantAndCartGuardTest extends TestCase
{
    use RefreshDatabase;

    public function test_inactive_variants_are_excluded_from_options_selection_and_cart(): void
    {
        $category = Category::create(['name' => 'Gamis', 'slug' => 'gamis']);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Gamis Safa',
            'slug' => 'gamis-safa',
            'sku' => 'GSF-01',
            'price' => 200000,
            'has_variants' => true,
            'stock' => 0,
            'is_active' => true,
            'channel_visibility' => 'online_only',
        ]);

        $colorAttr = Attribute::create(['name' => 'Warna', 'slug' => 'color', 'type' => 'select']);
        $navyOpt = AttributeOption::create(['attribute_id' => $colorAttr->id, 'value' => 'Navy', 'slug' => 'navy']);
        $dustyOpt = AttributeOption::create(['attribute_id' => $colorAttr->id, 'value' => 'Dusty', 'slug' => 'dusty']);

        $activeVariant = ProductVariant::create([
            'product_id' => $product->id,
            'name' => 'Gamis Safa - Navy',
            'sku' => 'GSF-01-NVY',
            'price' => 200000,
            'stock' => 5,
            'is_active' => true,
        ]);
        $activeVariant->attributeOptions()->attach($navyOpt->id);

        $inactiveVariant = ProductVariant::create([
            'product_id' => $product->id,
            'name' => 'Gamis Safa - Dusty',
            'sku' => 'GSF-01-DST',
            'price' => 200000,
            'stock' => 10,
            'is_active' => false,
        ]);
        $inactiveVariant->attributeOptions()->attach($dustyOpt->id);

        $component = Livewire::test(ProductDetail::class, ['slug' => 'gamis-safa']);

        $attributes = $component->instance()->availableAttributes();
        $colorValues = collect($attributes['color']['options'] ?? [])->pluck('value')->all();

        $this->assertSame(['Navy'], $colorValues);
        $this->assertFalse($component->instance()->isOptionAvailable('color', 'Dusty'));

        // Even if client tampers with selectedOptions to force inactive variant, addToCart must reject it
        $component->set('selectedOptions', ['color' => 'Dusty'])
            ->call('addToCart');

        $this->assertSame(0, CartItem::count());
    }

    public function test_add_to_cart_blocks_accumulated_quantity_exceeding_real_stock(): void
    {
        $user = User::factory()->create();
        $category = Category::create(['name' => 'Khimar', 'slug' => 'khimar']);

        // 1. Standalone product with stock = 3
        $standalone = Product::create([
            'category_id' => $category->id,
            'name' => 'Khimar Polos',
            'slug' => 'khimar-polos',
            'sku' => 'KHM-01',
            'price' => 95000,
            'has_variants' => false,
            'stock' => 3,
            'is_active' => true,
            'channel_visibility' => 'online_only',
        ]);

        $this->actingAs($user);

        // First add: qty 3 (allowed, cart now has 3/3)
        Livewire::test(ProductDetail::class, ['slug' => 'khimar-polos'])
            ->set('quantity', 3)
            ->call('addToCart');

        $cart = Cart::where('user_id', $user->id)->where('is_buy_now', false)->first();
        $this->assertNotNull($cart);
        $this->assertSame(3, (int) CartItem::where('cart_id', $cart->id)->where('product_id', $standalone->id)->value('quantity'));

        // Second add: qty 1 (must be blocked because 3 + 1 > 3)
        Livewire::test(ProductDetail::class, ['slug' => 'khimar-polos'])
            ->set('quantity', 1)
            ->call('addToCart');

        $this->assertSame(3, (int) CartItem::where('cart_id', $cart->id)->where('product_id', $standalone->id)->value('quantity'));

        // 2. Variant product with stock = 4
        $variantProduct = Product::create([
            'category_id' => $category->id,
            'name' => 'Khimar Motif',
            'slug' => 'khimar-motif',
            'sku' => 'KHM-02',
            'price' => 110000,
            'has_variants' => true,
            'stock' => 0,
            'is_active' => true,
            'channel_visibility' => 'online_only',
        ]);

        $sizeAttr = Attribute::create(['name' => 'Ukuran', 'slug' => 'size', 'type' => 'select']);
        $optL = AttributeOption::create(['attribute_id' => $sizeAttr->id, 'value' => 'L', 'slug' => 'l']);

        $variantL = ProductVariant::create([
            'product_id' => $variantProduct->id,
            'name' => 'Khimar Motif - L',
            'sku' => 'KHM-02-L',
            'price' => 110000,
            'stock' => 4,
            'is_active' => true,
        ]);
        $variantL->attributeOptions()->attach($optL->id);

        // Add 3 pcs first (allowed)
        Livewire::test(ProductDetail::class, ['slug' => 'khimar-motif'])
            ->call('selectOption', 'size', 'L')
            ->set('quantity', 3)
            ->call('addToCart');

        // Try adding 2 more pcs (3 + 2 = 5 > 4 -> blocked)
        Livewire::test(ProductDetail::class, ['slug' => 'khimar-motif'])
            ->call('selectOption', 'size', 'L')
            ->set('quantity', 2)
            ->call('addToCart');

        $this->assertSame(3, (int) CartItem::where('cart_id', $cart->id)->where('product_variant_id', $variantL->id)->value('quantity'));

        // Add 1 more pc (3 + 1 = 4 <= 4 -> allowed)
        Livewire::test(ProductDetail::class, ['slug' => 'khimar-motif'])
            ->call('selectOption', 'size', 'L')
            ->set('quantity', 1)
            ->call('addToCart');

        $this->assertSame(4, (int) CartItem::where('cart_id', $cart->id)->where('product_variant_id', $variantL->id)->value('quantity'));
    }

    public function test_increment_quantity_follows_real_stock_and_clamps_on_variant_switch(): void
    {
        $category = Category::create(['name' => 'Pashmina', 'slug' => 'pashmina']);

        // Standalone product with stock = 12 (must allow incrementing past old hardcoded limit of 10)
        Product::create([
            'category_id' => $category->id,
            'name' => 'Pashmina Silk',
            'slug' => 'pashmina-silk',
            'sku' => 'PSH-01',
            'price' => 75000,
            'has_variants' => false,
            'stock' => 12,
            'is_active' => true,
            'channel_visibility' => 'online_only',
        ]);

        $standaloneTest = Livewire::test(ProductDetail::class, ['slug' => 'pashmina-silk'])
            ->set('quantity', 10)
            ->call('incrementQuantity')
            ->assertSet('quantity', 11)
            ->call('incrementQuantity')
            ->assertSet('quantity', 12)
            ->call('incrementQuantity')
            ->assertSet('quantity', 12);

        // Variant product: Variant A stock 15, Variant B stock 2
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Pashmina Ceruty',
            'slug' => 'pashmina-ceruty',
            'sku' => 'PSH-02',
            'price' => 65000,
            'has_variants' => true,
            'stock' => 0,
            'is_active' => true,
            'channel_visibility' => 'online_only',
        ]);

        $colorAttr = Attribute::create(['name' => 'Warna', 'slug' => 'color', 'type' => 'select']);
        $blackOpt = AttributeOption::create(['attribute_id' => $colorAttr->id, 'value' => 'Hitam', 'slug' => 'hitam']);
        $creamOpt = AttributeOption::create(['attribute_id' => $colorAttr->id, 'value' => 'Cream', 'slug' => 'cream']);

        $vBlack = ProductVariant::create([
            'product_id' => $product->id,
            'name' => 'Pashmina Ceruty - Hitam',
            'sku' => 'PSH-02-BLK',
            'price' => 65000,
            'stock' => 15,
            'is_active' => true,
        ]);
        $vBlack->attributeOptions()->attach($blackOpt->id);

        $vCream = ProductVariant::create([
            'product_id' => $product->id,
            'name' => 'Pashmina Ceruty - Cream',
            'sku' => 'PSH-02-CRM',
            'price' => 65000,
            'stock' => 2,
            'is_active' => true,
        ]);
        $vCream->attributeOptions()->attach($creamOpt->id);

        // Select Hitam (stock 15), set qty 11 -> switch to Cream (stock 2) -> qty must clamp to 2
        Livewire::test(ProductDetail::class, ['slug' => 'pashmina-ceruty'])
            ->call('selectOption', 'color', 'Hitam')
            ->set('quantity', 11)
            ->call('incrementQuantity')
            ->assertSet('quantity', 12)
            ->call('selectOption', 'color', 'Cream')
            ->assertSet('quantity', 2)
            ->call('incrementQuantity')
            ->assertSet('quantity', 2);
    }

    public function test_deselecting_variant_resets_gallery_to_first_image(): void
    {
        $category = Category::create(['name' => 'Dress', 'slug' => 'dress']);

        $media1 = Media::create([
            'disk' => 'public',
            'name' => 'main-img',
            'path' => 'products/main-img.webp',
            'type' => 'image/webp',
            'ext' => 'webp',
            'width' => 800,
            'height' => 800,
            'size' => 10000,
        ]);

        $media2 = Media::create([
            'disk' => 'public',
            'name' => 'maroon-img',
            'path' => 'products/maroon-img.webp',
            'type' => 'image/webp',
            'ext' => 'webp',
            'width' => 800,
            'height' => 800,
            'size' => 10000,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Dress Raya',
            'slug' => 'dress-raya',
            'sku' => 'DRS-01',
            'price' => 250000,
            'has_variants' => true,
            'stock' => 0,
            'images' => [$media1->id, $media2->id],
            'is_active' => true,
            'channel_visibility' => 'online_only',
        ]);

        $colorAttr = Attribute::create(['name' => 'Warna', 'slug' => 'color', 'type' => 'select']);
        $maroonOpt = AttributeOption::create(['attribute_id' => $colorAttr->id, 'value' => 'Maroon', 'slug' => 'maroon']);

        $variantMaroon = ProductVariant::create([
            'product_id' => $product->id,
            'media_id' => $media2->id,
            'name' => 'Dress Raya - Maroon',
            'sku' => 'DRS-01-MRN',
            'price' => 250000,
            'stock' => 5,
            'is_active' => true,
        ]);
        $variantMaroon->attributeOptions()->attach($maroonOpt->id);

        Livewire::test(ProductDetail::class, ['slug' => 'dress-raya'])
            ->assertSet('activeGalleryIndex', 0)
            ->call('selectOption', 'color', 'Maroon')
            ->assertSet('activeGalleryIndex', 1)
            ->assertDispatched('change-gallery-image', index: 1)
            // Click Maroon again to deselect -> must reset to index 0
            ->call('selectOption', 'color', 'Maroon')
            ->assertSet('activeGalleryIndex', 0)
            ->assertDispatched('change-gallery-image', index: 0);
    }

    public function test_generate_variants_prevents_duplicate_attribute_combinations(): void
    {
        $admin = User::factory()->create();
        $category = Category::create(['name' => 'Tunik', 'slug' => 'tunik']);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Tunik Alina',
            'slug' => 'tunik-alina',
            'sku' => 'TNK-01',
            'price' => 175000,
            'has_variants' => true,
            'stock' => 0,
            'is_active' => true,
            'channel_visibility' => 'online_only',
        ]);

        $colorAttr = Attribute::create(['name' => 'Warna', 'slug' => 'color', 'type' => 'select']);
        $optNavy = AttributeOption::create(['attribute_id' => $colorAttr->id, 'value' => 'Navy', 'slug' => 'navy']);
        $optSage = AttributeOption::create(['attribute_id' => $colorAttr->id, 'value' => 'Sage', 'slug' => 'sage']);

        $this->actingAs($admin);

        // First run: creates 2 variants (Navy, Sage)
        Livewire::test(VariantsRelationManager::class, [
            'ownerRecord' => $product,
            'pageClass' => EditProduct::class,
        ])->callTableAction('generate_variants', data: [
            'attributes' => [
                [
                    'attribute_id' => $colorAttr->id,
                    'option_ids' => [$optNavy->id, $optSage->id],
                ],
            ],
        ]);

        $this->assertSame(2, $product->variants()->count());

        // Second run with same combinations: must skip both duplicates, count stays 2
        Livewire::test(VariantsRelationManager::class, [
            'ownerRecord' => $product,
            'pageClass' => EditProduct::class,
        ])->callTableAction('generate_variants', data: [
            'attributes' => [
                [
                    'attribute_id' => $colorAttr->id,
                    'option_ids' => [$optNavy->id, $optSage->id],
                ],
            ],
        ]);

        $this->assertSame(2, $product->variants()->count());
    }

    public function test_product_total_stock_and_out_of_stock_ignore_inactive_variants(): void
    {
        $category = Category::create(['name' => 'Abaya', 'slug' => 'abaya']);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Abaya Medina',
            'slug' => 'abaya-medina',
            'sku' => 'ABY-01',
            'price' => 300000,
            'has_variants' => true,
            'stock' => 0,
            'is_active' => true,
            'channel_visibility' => 'online_only',
        ]);

        $activeVariant = ProductVariant::create([
            'product_id' => $product->id,
            'name' => 'Abaya Medina - Jetblack',
            'sku' => 'ABY-01-JB',
            'price' => 300000,
            'stock' => 5,
            'is_active' => true,
        ]);

        ProductVariant::create([
            'product_id' => $product->id,
            'name' => 'Abaya Medina - Mocca',
            'sku' => 'ABY-01-MC',
            'price' => 300000,
            'stock' => 10,
            'is_active' => false,
        ]);

        $product->load('variants');
        $this->assertSame(5, $product->total_stock);
        $this->assertFalse($product->is_out_of_stock);

        // Nonaktifkan semua varian -> meski kolom stock masih > 0, total_stock harus 0 dan is_out_of_stock harus true
        $activeVariant->update(['is_active' => false]);
        $product->unsetRelation('variants')->load('variants');

        $this->assertSame(0, $product->total_stock);
        $this->assertTrue($product->is_out_of_stock);
    }

    public function test_cart_increment_quantity_respects_real_stock_and_variant_active_status(): void
    {
        $user = User::factory()->create();
        $category = Category::create(['name' => 'Bergo', 'slug' => 'bergo']);

        $standalone = Product::create([
            'category_id' => $category->id,
            'name' => 'Bergo Harian',
            'slug' => 'bergo-harian',
            'sku' => 'BRG-01',
            'price' => 60000,
            'has_variants' => false,
            'stock' => 2,
            'is_active' => true,
            'channel_visibility' => 'online_only',
        ]);

        $variantProduct = Product::create([
            'category_id' => $category->id,
            'name' => 'Bergo Sport',
            'slug' => 'bergo-sport',
            'sku' => 'BRG-02',
            'price' => 70000,
            'has_variants' => true,
            'stock' => 0,
            'is_active' => true,
            'channel_visibility' => 'online_only',
        ]);

        $inactiveVariant = ProductVariant::create([
            'product_id' => $variantProduct->id,
            'name' => 'Bergo Sport - Dusty',
            'sku' => 'BRG-02-DST',
            'price' => 70000,
            'stock' => 5,
            'is_active' => false,
        ]);

        $cart = Cart::create([
            'user_id' => $user->id,
            'is_buy_now' => false,
        ]);

        $standaloneItem = CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $standalone->id,
            'quantity' => 1,
        ]);

        $inactiveVariantItem = CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $variantProduct->id,
            'product_variant_id' => $inactiveVariant->id,
            'quantity' => 1,
        ]);

        $this->actingAs($user);

        // Standalone stock = 2: increment 1 -> 2 (diizinkan), lalu 2 -> 3 (ditolak)
        Livewire::test(CartComponent::class)
            ->call('incrementQuantity', $standaloneItem->id)
            ->call('incrementQuantity', $standaloneItem->id);

        $this->assertSame(2, (int) $standaloneItem->fresh()->quantity);

        // Varian non-aktif tidak boleh di-increment meski stok > 1
        Livewire::test(CartComponent::class)
            ->call('incrementQuantity', $inactiveVariantItem->id);

        $this->assertSame(1, (int) $inactiveVariantItem->fresh()->quantity);
    }
}
