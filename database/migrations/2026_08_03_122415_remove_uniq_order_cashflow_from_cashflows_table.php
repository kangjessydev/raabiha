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
        Schema::table('cashflows', function (Blueprint $table) {
            $table->index('order_id');
        });
        
        Schema::table('cashflows', function (Blueprint $table) {
            $table->dropUnique('uniq_order_cashflow');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cashflows', function (Blueprint $table) {
            $table->unique(['order_id', 'type', 'source'], 'uniq_order_cashflow');
        });
        
        Schema::table('cashflows', function (Blueprint $table) {
            $table->dropIndex(['order_id']);
        });
    }
};
