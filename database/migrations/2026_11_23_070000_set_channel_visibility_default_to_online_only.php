<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Non-destructive migration (ADR-001 + Opsi A):
     * Changes the default value of `channel_visibility` from `both` to `online_only`
     * on `products` and `product_variants` WITHOUT modifying existing legacy `both` rows.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE products MODIFY COLUMN channel_visibility ENUM('online_only', 'pos_only', 'both') NOT NULL DEFAULT 'online_only'");
            DB::statement("ALTER TABLE product_variants MODIFY COLUMN channel_visibility ENUM('online_only', 'pos_only', 'both') NOT NULL DEFAULT 'online_only'");

            return;
        }

        Schema::table('products', function (Blueprint $table) {
            $table->string('channel_visibility')->default('online_only')->change();
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->string('channel_visibility')->default('online_only')->change();
        });
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE products MODIFY COLUMN channel_visibility ENUM('online_only', 'pos_only', 'both') NOT NULL DEFAULT 'both'");
            DB::statement("ALTER TABLE product_variants MODIFY COLUMN channel_visibility ENUM('online_only', 'pos_only', 'both') NOT NULL DEFAULT 'both'");

            return;
        }

        Schema::table('products', function (Blueprint $table) {
            $table->string('channel_visibility')->default('both')->change();
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->string('channel_visibility')->default('both')->change();
        });
    }
};
