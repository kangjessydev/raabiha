<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Str;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        // Kategori
        $outerwear = Category::firstOrCreate(
            ['slug' => 'outerwear'],
            ['name' => 'Outerwear', 'description' => 'Koleksi jaket, mantel, dan luaran premium.']
        );
        $dresses = Category::firstOrCreate(
            ['slug' => 'dresses'],
            ['name' => 'Dresses & Tunics', 'description' => 'Gaun elegan dan tunik asimetris untuk gaya modis.']
        );
        $hijabs = Category::firstOrCreate(
            ['slug' => 'hijabs'],
            ['name' => 'Premium Hijabs', 'description' => 'Pashmina dan hijab segi empat dari sutra.']
        );

        // Produk 1: The Asymmetrical Tunic
        $tunic = Product::firstOrCreate(
            ['slug' => 'the-asymmetrical-tunic'],
            [
                'category_id' => $dresses->id,
                'name' => 'The Asymmetrical Tunic',
                'description' => '<p>Tunik elegan dengan potongan asimetris yang berani. Dibuat dengan katun poplin jepang yang sangat nyaman dipakai seharian.</p>',
                'price' => 850000,
                'reseller_price' => 750000,
                'weight' => 400,
                'has_variants' => true,
            ]
        );
        $tunic->variants()->firstOrCreate(['sku' => 'TUNIC-SLT-S'], ['name' => 'Slate Sand - S', 'stock' => 15, 'price' => 850000]);
        $tunic->variants()->firstOrCreate(['sku' => 'TUNIC-SLT-M'], ['name' => 'Slate Sand - M', 'stock' => 10, 'price' => 850000]);
        $tunic->variants()->firstOrCreate(['sku' => 'TUNIC-SLT-L'], ['name' => 'Slate Sand - L', 'stock' => 5, 'price' => 850000]);

        // Produk 2: Structured Trousers
        $trousers = Product::firstOrCreate(
            ['slug' => 'structured-trousers'],
            [
                'category_id' => $outerwear->id, // Atau buat kategori Bottoms
                'name' => 'Structured Trousers',
                'description' => '<p>Celana panjang bersiluet tegas dengan material wool blend yang tidak mudah kusut. Cocok dipadukan dengan tunik maupun kemeja kantor.</p>',
                'price' => 650000,
                'reseller_price' => 580000,
                'weight' => 500,
                'has_variants' => true,
            ]
        );
        $trousers->variants()->firstOrCreate(['sku' => 'TRS-CHR-S'], ['name' => 'Charcoal - S', 'stock' => 20, 'price' => 650000]);
        $trousers->variants()->firstOrCreate(['sku' => 'TRS-CHR-M'], ['name' => 'Charcoal - M', 'stock' => 15, 'price' => 650000]);

        // Produk 3: Modest Urban Coat
        $coat = Product::firstOrCreate(
            ['slug' => 'modest-urban-coat'],
            [
                'category_id' => $outerwear->id,
                'name' => 'Modest Urban Coat',
                'description' => '<p>Mantel panjang berpotongan longgar (oversized) yang dirancang untuk iklim tropis maupun sejuk. Lapisan dalamnya sangat halus di kulit.</p>',
                'price' => 1250000,
                'reseller_price' => 1100000,
                'weight' => 1200,
                'has_variants' => false,
            ]
        );

        // Produk 4: Silk Pashmina
        $pashmina = Product::firstOrCreate(
            ['slug' => 'pure-silk-pashmina'],
            [
                'category_id' => $hijabs->id,
                'name' => 'Pure Silk Pashmina',
                'description' => '<p>Pashmina berbahan sutra asli yang memberikan kilau mewah yang menawan, namun tidak licin saat dikenakan.</p>',
                'price' => 350000,
                'reseller_price' => 280000,
                'weight' => 150,
                'has_variants' => true,
            ]
        );
        $pashmina->variants()->firstOrCreate(['sku' => 'PSH-EMR'], ['name' => 'Emerald Green', 'stock' => 50, 'price' => 350000]);
        $pashmina->variants()->firstOrCreate(['sku' => 'PSH-SND'], ['name' => 'Desert Sand', 'stock' => 30, 'price' => 350000]);
    }
}
