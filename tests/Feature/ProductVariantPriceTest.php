<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProductVariantPriceTest extends TestCase
{
    use RefreshDatabase;

    protected function createProduct($attributes = [])
    {
        $category = Category::create([
            'name' => 'Dress',
            'slug' => 'dress',
        ]);

        return Product::create(array_merge([
            'category_id' => $category->id,
            'name' => 'Baju Raabiha',
            'slug' => 'baju-raabiha',
            'price' => 100000,
            'purchase_price' => 70000,
            'reseller_price' => 85000,
            'weight' => 500,
            'is_active' => true,
        ], $attributes));
    }

    public function test_variant_price_fallback_logic()
    {
        // 1. Create a parent product with default prices
        $product = $this->createProduct([
            'price' => 100000,
            'purchase_price' => 70000,
            'reseller_price' => 85000,
            'discount_price' => 90000, // Parent has discount price
        ]);

        // 2. Create a variant with empty (null) price fields
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'name' => 'Variant A',
            'stock' => 10,
            'price' => null,
            'purchase_price' => null,
            'reseller_price' => null,
            'discount_price' => null,
        ]);

        // Assert fallback to parent prices (except discount_price)
        $this->assertEquals(100000, $variant->price);
        $this->assertEquals(70000, $variant->purchase_price);
        $this->assertEquals(85000, $variant->reseller_price);
        $this->assertNull($variant->discount_price); // Does NOT inherit discount_price!

        // Selling price should be variant's price (which fell back to 100,000), NOT the parent's promo price
        $this->assertEquals(100000, $variant->selling_price);

        // Under guest/normal user, effective price should be the selling price (100,000)
        $this->assertEquals(100000, $variant->effective_price);

        // is_price_override should be false since prices are empty
        $this->assertFalse($variant->is_price_override);
    }

    public function test_variant_custom_price_logic()
    {
        $product = $this->createProduct([
            'price' => 100000,
            'purchase_price' => 70000,
            'reseller_price' => 85000,
        ]);

        // Create a variant with custom prices
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'name' => 'Variant B',
            'stock' => 10,
            'price' => 120000,
            'purchase_price' => 80000,
            'reseller_price' => 95000,
            'discount_price' => 110000,
        ]);

        // Assert custom prices are returned
        $this->assertEquals(120000, $variant->price);
        $this->assertEquals(80000, $variant->purchase_price);
        $this->assertEquals(95000, $variant->reseller_price);
        $this->assertEquals(110000, $variant->discount_price);

        // Selling price should be custom promo price
        $this->assertEquals(110000, $variant->selling_price);

        // is_price_override should automatically be true
        $this->assertTrue($variant->is_price_override);
    }

    public function test_variant_reseller_pricing_flow()
    {
        // Set up the reseller role
        Role::create(['name' => 'reseller']);

        $product = $this->createProduct([
            'price' => 100000,
            'reseller_price' => 85000,
        ]);

        // Case 1: Reseller logs in, variant has fallback reseller price
        $variantFallback = ProductVariant::create([
            'product_id' => $product->id,
            'name' => 'Variant A',
            'stock' => 10,
            'price' => null,
            'reseller_price' => null,
        ]);

        $resellerUser = User::factory()->create();
        $resellerUser->assignRole('reseller');
        Auth::login($resellerUser);

        $this->assertEquals(85000, $variantFallback->effective_price);

        // Case 2: Variant has custom reseller price
        $variantCustom = ProductVariant::create([
            'product_id' => $product->id,
            'name' => 'Variant B',
            'stock' => 10,
            'price' => null,
            'reseller_price' => 90000,
        ]);

        $this->assertEquals(90000, $variantCustom->effective_price);

        Auth::logout();
    }

    public function test_product_importer_saves_variant_with_nullable_prices()
    {
        $product = $this->createProduct([
            'name' => 'Baju Keren',
            'price' => 100000,
            'purchase_price' => 70000,
            'reseller_price' => 85000,
        ]);

        // Instantiate the importer
        $importer = new \App\Filament\Imports\ProductImporter(new \Filament\Actions\Imports\Models\Import(), [], []);
        
        // Use reflection to set protected/private property data
        $refImporter = new \ReflectionClass($importer);
        $propData = $refImporter->getProperty('data');
        $propData->setAccessible(true);
        
        // 1. Test variant with empty price fields
        $propData->setValue($importer, [
            'is_variant' => 'ya',
            'name' => 'Baju Keren', // Matches parent name
            'variant_name' => 'Warna Merah',
            'variant_sku' => 'BAJU-RED',
            'price' => '', // Empty price
            'discount_price' => '',
            'purchase_price' => '',
            'reseller_price' => '',
            'weight' => 500,
            'stock' => 12,
            'is_active' => '1',
        ]);
        
        $importer->saveRecord();

        $variant = ProductVariant::where('sku', 'BAJU-RED')->first();
        $this->assertNotNull($variant);
        $this->assertNull($variant->getRawOriginal('price'));
        $this->assertNull($variant->getRawOriginal('discount_price'));
        $this->assertNull($variant->getRawOriginal('purchase_price'));
        $this->assertNull($variant->getRawOriginal('reseller_price'));

        // Fallbacks should work
        $this->assertEquals(100000, $variant->price);
        $this->assertEquals(70000, $variant->purchase_price);
        $this->assertEquals(85000, $variant->reseller_price);

        // 2. Test variant with custom prices
        $propData->setValue($importer, [
            'is_variant' => 'ya',
            'name' => 'Baju Keren',
            'variant_name' => 'Warna Biru',
            'variant_sku' => 'BAJU-BLUE',
            'price' => '120000',
            'discount_price' => '110000',
            'purchase_price' => '80000',
            'reseller_price' => '95000',
            'weight' => 500,
            'stock' => 15,
            'is_active' => '1',
        ]);

        $importer->saveRecord();

        $variantBlue = ProductVariant::where('sku', 'BAJU-BLUE')->first();
        $this->assertNotNull($variantBlue);
        $this->assertEquals(120000, $variantBlue->getRawOriginal('price'));
        $this->assertEquals(110000, $variantBlue->getRawOriginal('discount_price'));
        $this->assertEquals(80000, $variantBlue->getRawOriginal('purchase_price'));
        $this->assertEquals(95000, $variantBlue->getRawOriginal('reseller_price'));
    }

    public function test_variant_export_retains_raw_nulls()
    {
        $product = $this->createProduct([
            'price' => 100000,
            'purchase_price' => 70000,
            'reseller_price' => 85000,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'name' => 'Variant A',
            'sku' => 'V-A',
            'stock' => 10,
            'price' => null, // Inherits
            'purchase_price' => null, // Inherits
            'reseller_price' => null, // Inherits
            'discount_price' => null,
        ]);

        // Emulate the export query and CSV generation logic in ExportMedia.php
        $rawPrice = $variant->getRawOriginal('price');
        $rawDiscount = $variant->getRawOriginal('discount_price');
        $rawPurchase = $variant->getRawOriginal('purchase_price');
        $rawReseller = $variant->getRawOriginal('reseller_price');

        $this->assertNull($rawPrice);
        $this->assertNull($rawDiscount);
        $this->assertNull($rawPurchase);
        $this->assertNull($rawReseller);
    }
}
