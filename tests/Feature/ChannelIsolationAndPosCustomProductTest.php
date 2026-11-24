<?php

namespace Tests\Feature;

use App\Filament\Imports\ProductImporter;
use App\Filament\Imports\ProductVariantImporter;
use App\Livewire\Search;
use App\Livewire\Shop;
use App\Models\Category;
use App\Models\PosSession;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockLog;
use App\Models\User;
use App\Services\PosTransactionService;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use ReflectionProperty;
use Tests\TestCase;

class ChannelIsolationAndPosCustomProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_pos_custom_and_pos_only_products_are_strictly_isolated_from_ecommerce(): void
    {
        $category = Category::create(['name' => 'Busana', 'slug' => 'busana']);

        $onlineProduct = Product::create([
            'category_id' => $category->id,
            'name' => 'Abaya Online Eksklusif',
            'slug' => 'abaya-online-eksklusif',
            'sku' => 'ONL-01',
            'price' => 250000,
            'stock' => 10,
            'is_active' => true,
            'is_hidden' => false,
            'is_custom' => false,
            'channel_visibility' => 'online_only',
        ]);

        $posOnlyProduct = Product::create([
            'category_id' => $category->id,
            'name' => 'Gamis Khusus Toko Fisik',
            'slug' => 'gamis-khusus-toko-fisik',
            'sku' => 'POS-01',
            'price' => 180000,
            'stock' => 5,
            'is_active' => true,
            'is_hidden' => false,
            'is_custom' => false,
            'channel_visibility' => 'pos_only',
        ]);

        $cashierCustomProduct = Product::create([
            'category_id' => $category->id,
            'name' => 'Produk Kustom Kasir Rahasia',
            'slug' => 'produk-kustom-kasir-rahasia',
            'sku' => 'CST-01',
            'price' => 99000,
            'stock' => 3,
            'is_active' => true,
            'is_hidden' => false,
            'is_custom' => true,
            'channel_visibility' => 'pos_only',
        ]);

        $legacyBothProduct = Product::create([
            'category_id' => $category->id,
            'name' => 'Produk Legacy Both Sementara',
            'slug' => 'produk-legacy-both-sementara',
            'sku' => 'LGC-01',
            'price' => 150000,
            'stock' => 8,
            'is_active' => true,
            'is_hidden' => false,
            'is_custom' => false,
            'channel_visibility' => 'both',
        ]);

        // 1. Home page: shows online & legacy both; hides pos_only & cashier custom
        $this->get('/')
            ->assertOk()
            ->assertSee($onlineProduct->name)
            ->assertSee($legacyBothProduct->name)
            ->assertDontSee($posOnlyProduct->name)
            ->assertDontSee($cashierCustomProduct->name);

        // 2. Shop page: shows online & legacy both; hides pos_only & cashier custom
        Livewire::test(Shop::class)
            ->assertSee($onlineProduct->name)
            ->assertSee($legacyBothProduct->name)
            ->assertDontSee($posOnlyProduct->name)
            ->assertDontSee($cashierCustomProduct->name);

        // 3. Search page: hides pos_only & cashier custom even when searching exact name
        Livewire::test(Search::class, ['q' => 'Kasir Rahasia'])
            ->assertDontSee($cashierCustomProduct->name);

        Livewire::test(Search::class, ['q' => 'Toko Fisik'])
            ->assertDontSee($posOnlyProduct->name);

        Livewire::test(Search::class, ['q' => 'Abaya Online'])
            ->assertSee($onlineProduct->name);

        // 4. ProductDetail direct URL: 200 for online_only & legacy both, 404 for pos_only & cashier custom
        $this->get('/product/'.$onlineProduct->slug)->assertOk();
        $this->get('/product/'.$legacyBothProduct->slug)->assertOk();
        $this->get('/product/'.$posOnlyProduct->slug)->assertNotFound();
        $this->get('/product/'.$cashierCustomProduct->slug)->assertNotFound();

        // 5. POS catalog scope: shows pos_only, cashier custom, & legacy both; hides online_only
        $posProductIds = Product::visibleInPos()->pluck('id')->all();
        $this->assertContains($posOnlyProduct->id, $posProductIds);
        $this->assertContains($cashierCustomProduct->id, $posProductIds);
        $this->assertContains($legacyBothProduct->id, $posProductIds);
        $this->assertNotContains($onlineProduct->id, $posProductIds);
    }

    public function test_new_product_defaults_to_online_only_and_importers_do_not_fallback_to_both(): void
    {
        $product = Product::create([
            'name' => 'Produk Baru Tanpa Channel',
            'slug' => 'produk-baru-tanpa-channel',
            'price' => 100000,
            'stock' => 5,
            'is_active' => true,
        ]);

        $product->refresh();
        $this->assertSame('online_only', $product->channel_visibility);

        // ProductImporter channel_visibility fallback must be online_only (not both)
        $prodChannelCol = collect(ProductImporter::getColumns())
            ->first(fn ($col) => $col->getName() === 'channel_visibility');
        $this->assertNotNull($prodChannelCol);

        $importer = new ProductImporter(new Import, ['channel_visibility' => 'Tampil Di (Channel)'], []);
        $recordProp = new ReflectionProperty($importer, 'record');
        $recordProp->setAccessible(true);
        $recordProp->setValue($importer, $product);

        $dataProp = new ReflectionProperty($importer, 'data');
        $dataProp->setAccessible(true);
        $dataProp->setValue($importer, ['is_custom' => false]);

        $prodChannelCol->importer($importer);
        $prodChannelCol->fillRecord('both');
        $this->assertSame('online_only', $product->channel_visibility);

        // If is_custom is true during import, force pos_only
        $dataProp->setValue($importer, ['is_custom' => true]);
        $prodChannelCol->fillRecord('online_only');
        $this->assertSame('pos_only', $product->channel_visibility);

        // ProductVariantImporter channel_visibility fallback must be online_only
        $varChannelCol = collect(ProductVariantImporter::getColumns())
            ->first(fn ($col) => $col->getName() === 'channel_visibility');
        $this->assertNotNull($varChannelCol);

        $variant = new ProductVariant(['product_id' => $product->id, 'name' => 'Varian A']);
        $varImporter = new ProductVariantImporter(new Import, ['channel_visibility' => 'Tampil Di (Channel)'], []);
        $varRecordProp = new ReflectionProperty($varImporter, 'record');
        $varRecordProp->setAccessible(true);
        $varRecordProp->setValue($varImporter, $variant);
        $varChannelCol->importer($varImporter);
        $varChannelCol->fillRecord('both');
        $this->assertSame('online_only', $variant->channel_visibility);
    }

    public function test_pos_auto_save_custom_product_sets_pos_only_and_deducts_stock_accurately(): void
    {
        $cashier = User::factory()->create();
        $session = PosSession::create([
            'cashier_id' => $cashier->id,
            'opened_at' => now(),
            'opening_cash' => 100000,
            'status' => 'open',
        ]);

        $service = app(PosTransactionService::class);
        $order = $service->completePosTransaction([
            'cashier_id' => $cashier->id,
            'pos_session_id' => $session->id,
            'customer_name' => 'Walk-in Test',
            'payment_method' => 'cash',
            'cash_paid' => 200000,
            'grand_total' => 150000,
            'items' => [
                [
                    'is_custom' => true,
                    'save_to_catalog' => true,
                    'name' => 'Kerudung Kustom Kasir',
                    'price' => 75000,
                    'purchase_price' => 40000,
                    'quantity' => 2,
                ],
            ],
        ]);

        $savedProduct = Product::where('name', 'Kerudung Kustom Kasir')->first();
        $this->assertNotNull($savedProduct);
        $this->assertTrue($savedProduct->is_custom);
        $this->assertSame('pos_only', $savedProduct->channel_visibility);
        // Stock must be 0 after selling 2 of 2 (no fictitious remaining stock!)
        $this->assertSame(0, (int) $savedProduct->stock);

        $stockLog = StockLog::where('product_id', $savedProduct->id)->where('type', 'out')->first();
        $this->assertNotNull($stockLog);
        $this->assertSame(-2, (int) $stockLog->quantity_change);
        $this->assertSame(0, (int) $stockLog->quantity_after);

        // Must NOT be accessible on E-Commerce
        $this->get('/product/'.$savedProduct->slug)->assertNotFound();
    }

    public function test_sitemap_excludes_pos_only_and_custom_products(): void
    {
        $category = Category::create(['name' => 'Sitemap Cat', 'slug' => 'sitemap-cat']);

        $online = Product::create([
            'category_id' => $category->id,
            'name' => 'Sitemap Online',
            'slug' => 'sitemap-online',
            'sku' => 'SM-ONL-01',
            'price' => 100000,
            'stock' => 5,
            'is_active' => true,
            'is_hidden' => false,
            'is_custom' => false,
            'channel_visibility' => 'online_only',
        ]);

        $posOnly = Product::create([
            'category_id' => $category->id,
            'name' => 'Sitemap POS',
            'slug' => 'sitemap-pos',
            'sku' => 'SM-POS-01',
            'price' => 100000,
            'stock' => 5,
            'is_active' => true,
            'is_hidden' => false,
            'is_custom' => false,
            'channel_visibility' => 'pos_only',
        ]);

        $legacyCustom = Product::create([
            'category_id' => $category->id,
            'name' => 'Sitemap Legacy Custom',
            'slug' => 'sitemap-legacy-custom',
            'sku' => 'SM-CST-01',
            'price' => 100000,
            'stock' => 5,
            'is_active' => true,
            'is_hidden' => false,
            'is_custom' => true,
            'channel_visibility' => 'both',
        ]);

        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $response->assertSee('/product/'.$online->slug, false);
        $response->assertDontSee('/product/'.$posOnly->slug, false);
        $response->assertDontSee('/product/'.$legacyCustom->slug, false);
    }

    public function test_moving_pos_custom_product_to_online_only_resets_is_custom_and_shows_on_storefront(): void
    {
        $product = Product::create([
            'name' => 'Soraya Abaya Textured',
            'slug' => 'soraya-abaya-textured',
            'sku' => 'SRY',
            'price' => 250000,
            'stock' => 10,
            'is_active' => true,
            'is_hidden' => false,
            'is_custom' => true,
            'channel_visibility' => 'pos_only',
        ]);

        $this->get('/product/'.$product->slug)->assertNotFound();

        $product->update(['channel_visibility' => 'online_only']);
        $product->refresh();

        $this->assertFalse((bool) $product->is_custom);
        $this->get('/product/'.$product->slug)->assertOk();
        Livewire::test(Shop::class)->assertSee($product->name);
    }
}
