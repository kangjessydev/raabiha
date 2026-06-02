<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attribute_option_product_variant', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variant_id')->constrained('product_variants')->cascadeOnDelete();
            $table->foreignId('attribute_option_id')->constrained('attribute_options')->cascadeOnDelete();
            // $table->primary(['product_variant_id', 'attribute_option_id'], 'attr_opt_prod_var_primary');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attribute_option_product_variant');
    }
};
