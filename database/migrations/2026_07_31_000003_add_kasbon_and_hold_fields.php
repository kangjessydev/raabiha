<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'due_amount')) {
                $table->decimal('due_amount', 12, 2)->default(0)->after('grand_total');
            }
            if (!Schema::hasColumn('orders', 'is_kasbon')) {
                $table->boolean('is_kasbon')->default(false)->after('due_amount');
            }
        });

        if (!Schema::hasTable('pos_held_carts')) {
            Schema::create('pos_held_carts', function (Blueprint $table) {
                $table->id();
                $table->string('hold_id', 50)->unique();
                $table->string('cashier_name')->nullable();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('customer_name')->nullable();
                $table->string('customer_phone')->nullable();
                $table->json('cart_data');
                $table->decimal('total', 12, 2)->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_held_carts');

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['due_amount', 'is_kasbon']);
        });
    }
};
