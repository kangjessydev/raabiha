<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->enum('channel_visibility', ['online_only', 'pos_only', 'both'])->default('both')->after('is_active');
            $table->decimal('pos_price', 12, 2)->nullable()->after('price');
            $table->decimal('pos_discount_price', 12, 2)->nullable()->after('pos_price');

            $table->index('channel_visibility');
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->enum('channel_visibility', ['online_only', 'pos_only', 'both'])->default('both')->after('is_active');
            $table->decimal('pos_price', 12, 2)->nullable()->after('price');
            $table->decimal('pos_discount_price', 12, 2)->nullable()->after('pos_price');

            $table->index('channel_visibility');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['channel_visibility']);
            $table->dropColumn(['channel_visibility', 'pos_price', 'pos_discount_price']);
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropIndex(['channel_visibility']);
            $table->dropColumn(['channel_visibility', 'pos_price', 'pos_discount_price']);
        });
    }
};
