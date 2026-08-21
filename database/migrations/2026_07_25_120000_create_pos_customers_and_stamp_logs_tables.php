<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_customers', function (Blueprint $table) {
            $table->id();
            $table->string('phone', 30)->unique()->index();
            $table->string('name')->nullable();
            $table->integer('stamp_count')->default(0);
            $table->integer('points_balance')->default(0);
            $table->integer('total_stamps_earned')->default(0);
            $table->integer('completed_cards_count')->default(0);
            $table->integer('total_visits')->default(0);
            $table->decimal('total_spent', 12, 2)->default(0);
            $table->timestamp('last_visit_at')->nullable();
            $table->timestamps();
        });

        Schema::create('pos_stamp_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pos_customer_id')->constrained('pos_customers')->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->enum('type', ['earned', 'redeemed', 'adjusted', 'expired'])->default('earned');
            $table->integer('stamps')->default(0);
            $table->integer('points')->default(0);
            $table->string('description')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_stamp_logs');
        Schema::dropIfExists('pos_customers');
    }
};
