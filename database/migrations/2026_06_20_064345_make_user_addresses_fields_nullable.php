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
        Schema::table('user_addresses', function (Blueprint $table) {
            $table->string('title')->nullable()->change();
            $table->string('recipient_name')->nullable()->change();
            $table->string('phone')->nullable()->change();
            $table->text('full_address')->nullable()->change();
            $table->string('province')->nullable()->change();
            $table->string('city')->nullable()->change();
            $table->string('district')->nullable()->change();
            $table->string('postal_code')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_addresses', function (Blueprint $table) {
            // Reverting to not null
            $table->string('title')->nullable(false)->change();
            $table->string('recipient_name')->nullable(false)->change();
            $table->string('phone')->nullable(false)->change();
            $table->text('full_address')->nullable(false)->change();
            $table->string('province')->nullable(false)->change();
            $table->string('city')->nullable(false)->change();
            $table->string('district')->nullable(false)->change();
            $table->string('postal_code')->nullable(false)->change();
        });
    }
};
