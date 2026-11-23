<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Daftar composite index performa (BKL-010).
     *
     * @var array<string, array<string, array<int, string>>>
     */
    protected array $compositeIndexes = [
        'products' => [
            'idx_products_channel_active' => ['is_active', 'channel_visibility', 'is_custom'],
        ],
        'product_variants' => [
            'idx_variants_product_active_stock' => ['product_id', 'is_active', 'stock'],
        ],
        'orders' => [
            'idx_orders_source_payment_created' => ['source', 'payment_status', 'created_at'],
            'idx_orders_status_created' => ['status', 'created_at'],
        ],
        'carts' => [
            'idx_carts_user_buy_now' => ['user_id', 'is_buy_now'],
            'idx_carts_session_buy_now' => ['session_id', 'is_buy_now'],
        ],
    ];

    public function up(): void
    {
        foreach ($this->compositeIndexes as $tableName => $indexes) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            foreach ($indexes as $indexName => $columns) {
                if (! Schema::hasColumns($tableName, $columns) || Schema::hasIndex($tableName, $indexName)) {
                    continue;
                }

                Schema::table($tableName, function (Blueprint $table) use ($columns, $indexName): void {
                    $table->index($columns, $indexName);
                });
            }
        }

        // Hapus tabel legacy tanpa referensi aktif (dengan persetujuan eksplisit user pada 2026-11-23)
        Schema::dropIfExists('coupons');
        Schema::dropIfExists('salespages');
    }

    public function down(): void
    {
        foreach ($this->compositeIndexes as $tableName => $indexes) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            foreach ($indexes as $indexName => $columns) {
                if (! Schema::hasIndex($tableName, $indexName)) {
                    continue;
                }

                Schema::table($tableName, function (Blueprint $table) use ($indexName): void {
                    $table->dropIndex($indexName);
                });
            }
        }
    }
};
