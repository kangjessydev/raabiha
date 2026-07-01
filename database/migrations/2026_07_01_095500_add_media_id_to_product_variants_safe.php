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
        if (!Schema::hasColumn('product_variants', 'media_id')) {
            Schema::table('product_variants', function (Blueprint $table) {
                $table->foreignId('media_id')->nullable()->constrained('curator')->nullOnDelete();
            });
        } else {
            // Column exists, add FK if missing
            try {
                Schema::table('product_variants', function (Blueprint $table) {
                    $table->foreign('media_id')->references('id')->on('curator')->nullOnDelete();
                });
            } catch (\Exception $e) {
                // Ignore if it already exists or another error occurs
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            if (Schema::hasColumn('product_variants', 'media_id')) {
                $table->dropForeign(['media_id']);
                $table->dropColumn('media_id');
            }
        });
    }
};
