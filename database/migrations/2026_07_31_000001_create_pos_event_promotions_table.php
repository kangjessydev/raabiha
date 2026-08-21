<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_event_promotions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable();
            $table->enum('discount_type', ['percent', 'fixed'])->default('percent');
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->enum('applies_to', ['all_items', 'specific_products', 'specific_categories'])->default('all_items');
            $table->json('included_product_ids')->nullable();
            $table->json('excluded_product_ids')->nullable();
            $table->json('included_category_ids')->nullable();
            $table->json('excluded_category_ids')->nullable();
            $table->decimal('min_purchase', 12, 2)->default(0);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_event_promotions');
    }
};
