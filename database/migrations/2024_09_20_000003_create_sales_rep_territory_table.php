<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_rep_territory', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_rep_id')->constrained('sales_reps')->cascadeOnDelete();
            $table->foreignId('sales_territory_id')->constrained('sales_territories')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_rep_territory');
    }
};
