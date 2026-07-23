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
            if (!Schema::hasColumn('orders', 'void_by_id')) {
                $table->foreignId('void_by_id')->nullable()->after('cashier_id')->constrained('users');
            }
            if (!Schema::hasColumn('orders', 'void_reason')) {
                $table->string('void_reason')->nullable()->after('void_by_id');
            }
            if (!Schema::hasColumn('orders', 'void_at')) {
                $table->timestamp('void_at')->nullable()->after('void_reason');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'void_by_id')) {
                $table->dropForeign(['void_by_id']);
                $table->dropColumn(['void_by_id', 'void_reason', 'void_at']);
            }
        });
    }
};
