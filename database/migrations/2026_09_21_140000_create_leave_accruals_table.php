<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_accrual_runs', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('accrual_year');
            $table->unsignedTinyInteger('accrual_month');
            $table->enum('status', ['running', 'completed', 'failed'])->default('running');
            $table->unsignedInteger('accruals_created')->default(0);
            $table->unsignedInteger('accruals_skipped')->default(0);
            $table->unsignedInteger('employees_ineligible')->default(0);
            $table->text('message')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->index(['accrual_year', 'accrual_month']);
        });

        Schema::create('leave_accruals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('accrual_year');
            $table->unsignedTinyInteger('accrual_month');
            $table->decimal('days', 4, 1)->default(1);
            $table->string('source', 32)->default('scheduled');
            $table->foreignId('run_id')->nullable()->constrained('leave_accrual_runs')->nullOnDelete();
            $table->timestamp('accrued_at');
            $table->timestamps();

            $table->unique(['employee_id', 'accrual_year', 'accrual_month'], 'leave_accruals_employee_period_unique');
            $table->index(['accrual_year', 'accrual_month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_accruals');
        Schema::dropIfExists('leave_accrual_runs');
    }
};
