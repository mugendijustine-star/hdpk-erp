<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('employee_allowances_fixed', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('type');
            $table->text('amount_enc');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_allowances_fixed');
    }
};
