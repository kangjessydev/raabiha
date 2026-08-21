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
        Schema::create('pos_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cashier_id')->constrained('users');
            $table->dateTime('opened_at');
            $table->dateTime('closed_at')->nullable();
            $table->decimal('opening_cash', 12, 2)->default(0.00);
            $table->decimal('expected_ending_cash', 12, 2)->nullable();
            $table->decimal('actual_ending_cash', 12, 2)->nullable();
            $table->decimal('difference_cash', 12, 2)->nullable();
            $table->enum('status', ['open', 'closed'])->default('open');
            // Virtual column untuk unique constraint "satu shift open per kasir"
            $table->unsignedBigInteger('open_session_marker')->nullable()->virtualAs("CASE WHEN status = 'open' THEN cashier_id ELSE NULL END");
            $table->text('notes')->nullable();
            $table->timestamps();

            // Unique index pada virtual column
            $table->unique('open_session_marker', 'idx_one_open_shift_per_cashier');
            $table->index(['cashier_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pos_sessions');
    }
};
