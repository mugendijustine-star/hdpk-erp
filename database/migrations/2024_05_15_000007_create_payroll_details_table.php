<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payroll_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_run_id')->constrained('payroll_runs')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees');
            $table->text('basic_salary_enc')->nullable();
            $table->text('fixed_allowances_enc')->nullable();
            $table->text('variable_allowances_enc')->nullable();
            $table->text('overtime_enc')->nullable();
            $table->text('deductions_enc')->nullable();
            $table->text('net_pay_enc')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_details');
    }
};
