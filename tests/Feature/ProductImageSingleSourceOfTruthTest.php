<?php

namespace Tests\Feature;

use App\Filament\Imports\ProductImporter;
use App\Livewire\Cart as CartComponent;
use App\Livewire\Checkout as CheckoutComponent;
use App\Livewire\ProductDetail;
use App\Livewire\Shop;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Media;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\ProductImageResolver;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProductImageSingleSourceOfTruthTest extends TestCase
{
    use RefreshDatabase;

    private function createMedia(string $name, string $path): Media
    {
        return Media::create([
            'disk' => 'public',
            'name' => $name,
            'path' => $path,
            'type' => 'image/webp',
            'ext' => 'webp',
            'width' => 800,
            'height' => 800,
            'size' => 12000,
        ]);
    }

    public function test_standalone_and_variant_products_render_images_across_shop_detail_cart_and_checkout(): void
    {
        $category = Category::create(['name' => 'Gamis', 'slug' => 'gamis']);

        $parentMedia = $this->createMedia('gamis-parent', 'products/gamis-parent.webp');
        $variantMedia = $this->createMedia('gamis-maroon', 'products/gamis-maroon.webp');

        // Standalone product with URL string format
        $standalone = Product::create([
            'category_id' => $category->id,
            'name' => 'Tunik Polos',
            'slug' => 'tunik-polos',
            'sku' => 'TNK-01',
            'price' => 120000,
            'stock' => 10,
            'has_variants' => false,
            'channel_visibility' => 'online_only',
            'is_active' => true,
            'is_hidden' => false,
            'images' => ['https://www.raabiha.com/storage/products/gamis-parent.webp'],
        ]);

        // Product with variants (parent uses numeric Curator ID)
        $parent = Product::create([
            'category_id' => $category->id,
            'name' => 'Gamis Varian',
            'slug' => 'gamis-varian',
            'sku' => 'GMV-01',
            'price' => 200000,
            'stock' => 0,
            'has_variants' => true,
            'channel_visibility' => 'online_only',
            'is_active' => true,
            'is_hidden' => false,
            'images' => [$parentMedia->id, $variantMedia->id],
        ]);

        $variant = ProductVariant::create([
            'product_id' => $parent->id,
            'media_id' => $variantMedia->id,
            'name' => 'Maroon L',
            'sku' => 'GMV-01-MRN',
            'price' => 210000,
            'stock' => 5,
            'is_active' => true,
        ]);

        // Product without images -> must fallback to placeholder.webp (not placeholder.png)
        $noImageProduct = Product::create([
            'category_id' => $category->id,
            'name' => 'Produk Tanpa Foto',
            'slug' => 'produk-tanpa-foto',
            'sku' => 'NOIMG-01',
            'price' => 50000,
            'stock' => 5,
            'has_variants' => false,
            'channel_visibility' => 'online_only',
            'is_active' => true,
            'is_hidden' => false,
            'images' => null,
        ]);

        // 1. Shop renders both URL-string & Curator-ID products
        Livewire::test(Shop::class)
            ->assertSee('https://www.raabiha.com/storage/products/gamis-parent.webp', false)
            ->assertSee($parentMedia->url, false)
            ->assertSee('assets/images/placeholder.webp', false);

        // 2. ProductDetail fallback uses placeholder.webp (never placeholder.png)
        Livewire::test(ProductDetail::class, ['slug' => 'produk-tanpa-foto'])
            ->assertSet('galleryUrls', [asset('assets/images/placeholder.webp')]);

        // 3. Cart & Checkout prioritize variant photo and resolve URL-string standalone product
        $user = User::factory()->create();
        $cart = Cart::create(['user_id' => $user->id, 'is_buy_now' => false]);

        $cartItemStandalone = CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $standalone->id,
            'product_variant_id' => null,
            'quantity' => 1,
        ]);

        $cartItemVariant = CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $parent->id,
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $this->actingAs($user);

        Livewire::test(CartComponent::class)
            ->assertSee('https://www.raabiha.com/storage/products/gamis-parent.webp', false)
            ->assertSee($variantMedia->url, false);

        Livewire::test(CheckoutComponent::class)
            ->assertSee('https://www.raabiha.com/storage/products/gamis-parent.webp', false)
            ->assertSee($variantMedia->url, false);
    }

    public function test_normalize_product_images_command_is_idempotent_and_preserves_unmatched_urls(): void
    {
        $category = Category::create(['name' => 'Aksesoris', 'slug' => 'aksesoris']);

        // Simulate App\Models\Media stripping extension from `name`
        $media1 = $this->createMedia('ae30c84a-1111', 'media/ae30c84a-1111.webp');
        $media2 = $this->createMedia('bf40d95b-2222', 'products/bf40d95b-2222.webp');

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Bros Cantik',
            'slug' => 'bros-cantik',
            'sku' => 'BRS-01',
            'price' => 35000,
            'stock' => 20,
            'has_variants' => false,
            'is_active' => true,
            'images' => [
                'https://www.raabiha.com/storage/media/ae30c84a-1111.webp',
                'products/bf40d95b-2222.webp',
                'https://cdn.example.com/unmatched-external.webp',
            ],
        ]);

        // 1. Dry-run does not modify database
        $this->artisan('products:normalize-images', ['--dry-run' => true])
            ->expectsOutputToContain('Mode DRY-RUN aktif')
            ->expectsOutputToContain('Produk diperbarui  : 1')
            ->expectsOutputToContain('Item dikonversi    : 2')
            ->expectsOutputToContain('Item tak cocok     : 1')
            ->assertExitCode(0);

        $product->refresh();
        $this->assertSame('https://www.raabiha.com/storage/media/ae30c84a-1111.webp', $product->images[0]);

        // 2. Live run converts matched URLs/paths to Curator IDs and keeps unmatched URL (zero data loss)
        $this->artisan('products:normalize-images')
            ->expectsOutputToContain('Produk diperbarui  : 1')
            ->expectsOutputToContain('Item dikonversi    : 2')
            ->expectsOutputToContain('Item tak cocok     : 1')
            ->assertExitCode(0);

        $product->refresh();
        $this->assertSame([
            $media1->id,
            $media2->id,
            'https://cdn.example.com/unmatched-external.webp',
        ], $product->images);

        // 3. Re-running is idempotent (0 products updated)
        $this->artisan('products:normalize-images')
            ->expectsOutputToContain('Produk diperbarui  : 0')
            ->expectsOutputToContain('Item dikonversi    : 0')
            ->assertExitCode(0);
    }

    public function test_product_importer_images_column_normalizes_urls_and_ignores_blank_state(): void
    {
        $media = $this->createMedia('import-photo', 'products/import-photo.webp');
        $category = Category::create(['name' => 'Khimar', 'slug' => 'khimar']);

        $existingProduct = Product::create([
            'category_id' => $category->id,
            'name' => 'Khimar Syari',
            'slug' => 'khimar-syari',
            'sku' => 'KHM-01',
            'price' => 95000,
            'stock' => 15,
            'has_variants' => false,
            'is_active' => true,
            'images' => [$media->id],
        ]);

        $imagesColumn = collect(ProductImporter::getColumns())
            ->first(fn ($col) => $col->getName() === 'images');

        $this->assertNotNull($imagesColumn);
        $this->assertTrue($imagesColumn->isBlankStateIgnored());

        // castState converts CSV URL string directly into integer Curator ID
        $casted = $imagesColumn->castState('https://www.raabiha.com/storage/products/import-photo.webp');
        $this->assertSame([$media->id], $casted);

        // Blank CSV state casts to null and does not wipe existing product images
        $this->assertNull($imagesColumn->castState(''));

        $importModel = new Import;
        $importer = new ProductImporter($importModel, ['images' => 'Gambar'], []);
        $reflection = new \ReflectionProperty($importer, 'record');
        $reflection->setAccessible(true);
        $reflection->setValue($importer, $existingProduct);

        $imagesColumn->importer($importer);
        $imagesColumn->fillRecord(null);
        $this->assertSame([$media->id], $existingProduct->images);
    }

    public function test_curator_picker_helper_resolves_legacy_urls_and_preserves_unmatched_for_zero_data_loss(): void
    {
        $media1 = $this->createMedia('dress-a', 'products/dress-a.webp');
        $media2 = $this->createMedia('dress-b', 'products/dress-b.webp');

        // Legacy URL strings matching Curator resolve to exact ordered Curator IDs
        $resolvedIds = ProductImageResolver::resolveMediaIds([
            'https://www.raabiha.com/storage/products/dress-b.webp',
            'https://www.raabiha.com/storage/products/dress-a.webp',
        ]);
        $this->assertSame([$media2->id, $media1->id], $resolvedIds);

        // Unmatched legacy URLs return empty resolvedIds (triggering ProductForm zero-data-loss guard)
        $unmatchedIds = ProductImageResolver::resolveMediaIds([
            'https://cdn.example.com/orphaned-image.webp',
        ]);
        $this->assertSame([], $unmatchedIds);
    }
}
