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
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('pos_session_id')->nullable()->after('source')->constrained('pos_sessions');
            $table->foreignId('cashier_id')->nullable()->after('user_id')->constrained('users');
            $table->string('customer_name')->nullable()->after('cashier_id');
            $table->string('customer_phone', 20)->nullable()->after('customer_name');
            $table->decimal('cash_paid', 12, 2)->nullable()->after('grand_total');
            $table->decimal('cash_change', 12, 2)->nullable()->after('cash_paid');
            $table->json('payment_details')->nullable()->after('cash_change');

            // Tambahan Index untuk performa
            $table->index('source');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['pos_session_id']);
            $table->dropForeign(['cashier_id']);
            $table->dropIndex(['source']);
            $table->dropColumn([
                'pos_session_id',
                'cashier_id',
                'customer_name',
                'customer_phone',
                'cash_paid',
                'cash_change',
                'payment_details'
            ]);
        });
    }
};
