<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DatabasePerformanceIndexesAndCleanupTest extends TestCase
{
    use RefreshDatabase;

    public function test_unused_legacy_tables_are_dropped(): void
    {
        $this->assertFalse(Schema::hasTable('coupons'));
        $this->assertFalse(Schema::hasTable('salespages'));
    }

    public function test_composite_performance_indexes_exist_on_core_tables(): void
    {
        $this->assertTrue(Schema::hasIndex('products', 'idx_products_channel_active'));
        $this->assertTrue(Schema::hasIndex('product_variants', 'idx_variants_product_active_stock'));
        $this->assertTrue(Schema::hasIndex('orders', 'idx_orders_source_payment_created'));
        $this->assertTrue(Schema::hasIndex('orders', 'idx_orders_status_created'));
        $this->assertTrue(Schema::hasIndex('carts', 'idx_carts_user_buy_now'));
        $this->assertTrue(Schema::hasIndex('carts', 'idx_carts_session_buy_now'));
    }
}
