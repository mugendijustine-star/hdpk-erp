<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('production_outputs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_batch_id')->constrained('production_batches')->cascadeOnDelete();
            $table->foreignId('product_variant_id')->constrained('product_variants');
            $table->foreignId('machine_id')->nullable()->constrained('machines');
            $table->text('qty_good_enc')->nullable();
            $table->text('qty_waste_enc')->nullable();
            $table->string('waste_reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_outputs');
    }
};
