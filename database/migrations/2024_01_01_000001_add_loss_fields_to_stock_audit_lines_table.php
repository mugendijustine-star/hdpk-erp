<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('stock_audit_lines', function (Blueprint $table) {
            $table->string('loss_type')->nullable();
            $table->foreignId('responsible_user_id')->nullable()->constrained('users');
            $table->text('manager_comment')->nullable();
            $table->text('admin_comment')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_audit_lines', function (Blueprint $table) {
            $table->dropForeign(['responsible_user_id']);
            $table->dropColumn(['loss_type', 'responsible_user_id', 'manager_comment', 'admin_comment']);
        });
    }
};
