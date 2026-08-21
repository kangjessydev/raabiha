<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Drop pos_debt_payments table
        Schema::dropIfExists('pos_debt_payments');

        // 2. Remove kasbon columns from orders
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'is_kasbon')) {
                $table->dropColumn('is_kasbon');
            }
            if (Schema::hasColumn('orders', 'due_amount')) {
                $table->dropColumn('due_amount');
            }
        });
    }

    public function down(): void
    {
        // Recreate pos_debt_payments
        Schema::create('pos_debt_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('pos_customer_id')->nullable()->constrained('pos_customers')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('pos_session_id')->nullable()->constrained('pos_sessions')->nullOnDelete();
            $table->decimal('amount_paid', 12, 2);
            $table->string('payment_method')->default('cash');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Restore columns on orders
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('due_amount', 12, 2)->default(0)->after('grand_total');
            $table->boolean('is_kasbon')->default(false)->after('due_amount');
        });
    }
};
