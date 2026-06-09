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
            $table->enum('source', ['order', 'manual'])->default('manual')->after('order_id');
            $table->boolean('is_reversed')->default(false)->after('source');
            $table->string('reversal_note')->nullable()->after('is_reversed');

            // Performance indexes untuk aggregate query di widget
            $table->index(['type', 'source', 'is_reversed'], 'idx_cashflows_summary');
            $table->index('transaction_date', 'idx_cashflows_date');
            $table->unique(['order_id', 'type', 'source'], 'uniq_order_cashflow');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cashflows', function (Blueprint $table) {
            $table->dropUnique('uniq_order_cashflow');
            $table->dropIndex('idx_cashflows_date');
            $table->dropIndex('idx_cashflows_summary');
            $table->dropColumn(['source', 'is_reversed', 'reversal_note']);
        });
    }
};
