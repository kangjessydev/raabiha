<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'is_reserved')) {
                $table->boolean('is_reserved')->default(false)->after('is_kasbon');
            }
            if (!Schema::hasColumn('orders', 'pickup_date')) {
                $table->date('pickup_date')->nullable()->after('is_reserved');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'is_reserved')) {
                $table->dropColumn('is_reserved');
            }
            if (Schema::hasColumn('orders', 'pickup_date')) {
                $table->dropColumn('pickup_date');
            }
        });
    }
};
