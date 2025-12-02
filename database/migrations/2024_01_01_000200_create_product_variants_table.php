<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnDelete();
            $table->string('size')->nullable();
            $table->string('colour')->nullable();
            $table->string('sku')->unique();
            $table->string('barcode')->unique();
            $table->text('cost_enc')->nullable();
            $table->text('selling_price_enc')->nullable();
            $table->text('stock_qty_enc')->nullable();
            $table->text('reorder_level_enc')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
