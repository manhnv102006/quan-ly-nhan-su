<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leader_team_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('leader_employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('manager_user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('period_month');
            $table->unsignedSmallInteger('period_year');
            $table->string('title');
            $table->text('work_progress');
            $table->text('team_results');
            $table->text('notes')->nullable();
            $table->unsignedInteger('member_count')->default(0);
            $table->unsignedInteger('kpi_total')->default(0);
            $table->unsignedInteger('kpi_completed')->default(0);
            $table->decimal('avg_kpi_progress', 5, 2)->default(0);
            $table->unsignedInteger('total_work_days')->default(0);
            $table->unsignedInteger('total_late_days')->default(0);
            $table->string('status', 20)->default('draft');
            $table->timestamp('submitted_at')->nullable();
            $table->text('manager_review')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['leader_employee_id', 'period_month', 'period_year'], 'leader_team_reports_period_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leader_team_reports');
    }
};
