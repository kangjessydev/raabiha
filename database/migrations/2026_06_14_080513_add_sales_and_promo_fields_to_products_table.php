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
            $table->decimal('rating', 3, 1)->default(0.0)->after('price');
            $table->unsignedInteger('sold_count')->default(0)->after('rating');
            $table->boolean('has_free_shipping')->default(false)->after('promo_rules');
            $table->unsignedTinyInteger('cashback_percent')->nullable()->after('has_free_shipping');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['rating', 'sold_count', 'has_free_shipping', 'cashback_percent']);
        });
    }
};
