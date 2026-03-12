<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payroll_id')->nullable();
            $table->string('kashf_no', 64)->nullable();
            $table->string('action', 64);
            $table->text('description')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_name')->nullable();
            $table->string('ip_address', 64)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index(['payroll_id'], 'idx_payroll_audit_payroll_id');
            $table->index(['kashf_no'], 'idx_payroll_audit_kashf_no');
            $table->index(['action'], 'idx_payroll_audit_action');
            $table->index(['user_id'], 'idx_payroll_audit_user_id');
            $table->index(['created_at'], 'idx_payroll_audit_created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_audit_logs');
    }
};
