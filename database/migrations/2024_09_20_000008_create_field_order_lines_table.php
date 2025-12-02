<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('field_order_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('field_order_id')->constrained('field_orders')->cascadeOnDelete();
            $table->foreignId('product_variant_id')->constrained('product_variants');
            $table->text('qty_enc')->nullable();
            $table->text('unit_price_enc')->nullable();
            $table->text('line_total_enc')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('field_order_lines');
    }
};
