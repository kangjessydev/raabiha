<?php

namespace Tests\Feature;

use App\Livewire\ProductDetail;
use App\Models\Attribute;
use App\Models\AttributeOption;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Awcodes\Curator\Models\Media;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class VariantImageMatchingTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_detail_resolves_gallery_urls_and_dispatches_change_gallery_image_on_variant_selection()
    {
        $category = Category::create([
            'name' => 'Fashion',
            'slug' => 'fashion',
        ]);

        $media1 = Media::create([
            'disk' => 'public',
            'name' => 'navy-dress.webp',
            'path' => 'products/navy-dress.webp',
            'type' => 'image/webp',
            'ext' => 'webp',
            'width' => 800,
            'height' => 800,
            'size' => 12000,
        ]);

        $media2 = Media::create([
            'disk' => 'public',
            'name' => 'maroon-dress.webp',
            'path' => 'products/maroon-dress.webp',
            'type' => 'image/webp',
            'ext' => 'webp',
            'width' => 800,
            'height' => 800,
            'size' => 12000,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Gamis Cantik',
            'slug' => 'gamis-cantik',
            'sku' => 'GMS-01',
            'price' => 150000,
            'has_variants' => true,
            'stock' => 0,
            'images' => [
                'https://www.raabiha.com/storage/products/navy-dress.webp',
                'https://www.raabiha.com/storage/products/maroon-dress.webp',
            ],
            'is_active' => true,
        ]);

        $colorAttr = Attribute::create([
            'name' => 'Warna',
            'slug' => 'color',
            'type' => 'select',
        ]);

        $navyOpt = AttributeOption::create([
            'attribute_id' => $colorAttr->id,
            'value' => 'Navy',
            'slug' => 'navy',
        ]);

        $maroonOpt = AttributeOption::create([
            'attribute_id' => $colorAttr->id,
            'value' => 'Maroon',
            'slug' => 'maroon',
        ]);

        $variantNavy = ProductVariant::create([
            'product_id' => $product->id,
            'media_id' => $media1->id,
            'name' => 'Gamis Cantik - Navy',
            'sku' => 'GMS-01-NVY',
            'price' => 150000,
            'stock' => 10,
            'is_active' => true,
        ]);
        $variantNavy->attributeOptions()->attach($navyOpt->id);

        $variantMaroon = ProductVariant::create([
            'product_id' => $product->id,
            'media_id' => $media2->id,
            'name' => 'Gamis Cantik - Maroon',
            'sku' => 'GMS-01-MRN',
            'price' => 150000,
            'stock' => 5,
            'is_active' => true,
        ]);
        $variantMaroon->attributeOptions()->attach($maroonOpt->id);

        // Test Livewire component with string URLs
        Livewire::test(ProductDetail::class, ['slug' => 'gamis-cantik'])
            ->assertSet('galleryUrls', [
                'https://www.raabiha.com/storage/products/navy-dress.webp',
                'https://www.raabiha.com/storage/products/maroon-dress.webp',
            ])
            ->assertSet('galleryMediaIds', [
                $media1->id,
                $media2->id,
            ])
            ->call('selectOption', 'color', 'Maroon')
            ->assertDispatched('change-gallery-image', index: 1)
            ->call('selectOption', 'color', 'Navy')
            ->assertDispatched('change-gallery-image', index: 0);

        // Test with numeric Curator IDs
        $product->update(['images' => [$media1->id, $media2->id]]);

        Livewire::test(ProductDetail::class, ['slug' => 'gamis-cantik'])
            ->assertSet('galleryMediaIds', [
                $media1->id,
                $media2->id,
            ])
            ->call('selectOption', 'color', 'Maroon')
            ->assertDispatched('change-gallery-image', index: 1);
    }
}
