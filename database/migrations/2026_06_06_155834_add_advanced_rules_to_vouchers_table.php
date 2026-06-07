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
        Schema::table('vouchers', function (Blueprint $table) {
            $table->boolean('is_shipping_voucher')->default(false)->after('discount_amount');
            $table->integer('min_items')->default(0)->after('min_purchase');
            $table->boolean('exclude_resellers')->default(true)->after('specific_users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->dropColumn(['is_shipping_voucher', 'min_items', 'exclude_resellers']);
        });
    }
};
