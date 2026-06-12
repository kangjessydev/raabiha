<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add purchase_price to products
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('purchase_price', 15, 2)->nullable()->after('price');
        });

        // 2. Add purchase_price to product_variants
        Schema::table('product_variants', function (Blueprint $table) {
            $table->decimal('purchase_price', 15, 2)->nullable()->after('price');
        });

        // 3. Add purchase_price to order_items
        Schema::table('order_items', function (Blueprint $table) {
            $table->decimal('purchase_price', 15, 2)->nullable()->after('price');
        });

        // 4. Data Seeder: Populate existing records with a smart default (e.g., 70% of retail price)
        try {
            // Seed products
            DB::table('products')->orderBy('id')->chunk(100, function ($products) {
                foreach ($products as $product) {
                    $purchasePrice = $product->price * 0.70;
                    DB::table('products')
                        ->where('id', $product->id)
                        ->update(['purchase_price' => $purchasePrice]);
                }
            });

            // Seed variants
            DB::table('product_variants')->orderBy('id')->chunk(100, function ($variants) {
                foreach ($variants as $variant) {
                    $product = DB::table('products')->where('id', $variant->product_id)->first();
                    $retailPrice = $variant->price ?? ($product ? $product->price : 0);
                    $purchasePrice = $retailPrice * 0.70;
                    DB::table('product_variants')
                        ->where('id', $variant->id)
                        ->update(['purchase_price' => $purchasePrice]);
                }
            });

            // Seed order items based on their product/variant
            DB::table('order_items')->orderBy('id')->chunk(100, function ($orderItems) {
                foreach ($orderItems as $item) {
                    $purchasePrice = null;
                    if ($item->product_variant_id) {
                        $variant = DB::table('product_variants')->where('id', $item->product_variant_id)->first();
                        if ($variant) {
                            $purchasePrice = $variant->purchase_price;
                        }
                    }
                    if (!$purchasePrice) {
                        $product = DB::table('products')->where('id', $item->product_id)->first();
                        if ($product) {
                            $purchasePrice = $product->purchase_price;
                        }
                    }
                    if ($purchasePrice) {
                        DB::table('order_items')
                            ->where('id', $item->id)
                            ->update(['purchase_price' => $purchasePrice]);
                    }
                }
            });
        } catch (\Exception $e) {
            // Log warning or handle exception but don't crash migration if tables are empty/corrupt
            report($e);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('purchase_price');
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn('purchase_price');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('purchase_price');
        });
    }
};
