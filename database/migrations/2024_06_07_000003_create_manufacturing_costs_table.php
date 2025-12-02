<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('manufacturing_costs', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('type');
            $table->string('description')->nullable();
            $table->text('amount_enc')->nullable();
            $table->foreignId('user_id')->nullable()->constrained();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manufacturing_costs');
    }
};
