<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('apcu_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variant_id')->constrained('product_variants')->cascadeOnDelete();
            $table->date('date');
            $table->text('total_cost_enc')->nullable();
            $table->text('units_produced_enc')->nullable();
            $table->text('apcu_enc')->nullable();
            $table->boolean('editable')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('apcu_records');
    }
};
