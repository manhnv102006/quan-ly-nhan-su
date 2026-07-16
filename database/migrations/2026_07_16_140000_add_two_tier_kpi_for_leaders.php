<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kpi_assignments', function (Blueprint $table) {
            $table->foreignId('leader_employee_id')
                ->nullable()
                ->after('manager_id')
                ->constrained('employees')
                ->nullOnDelete();
            $table->timestamp('leader_assigned_at')->nullable()->after('leader_employee_id');
        });

        Schema::table('employee_kpis', function (Blueprint $table) {
            $table->decimal('leader_score', 5, 2)->nullable()->after('score');
            $table->text('leader_review')->nullable()->after('leader_score');
        });

        Schema::create('kpi_team_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained('kpi_assignments')->cascadeOnDelete();
            $table->foreignId('leader_employee_id')->constrained('employees')->cascadeOnDelete();
            $table->text('summary')->nullable();
            $table->unsignedInteger('total_members')->default(0);
            $table->unsignedInteger('completed_count')->default(0);
            $table->decimal('avg_progress', 5, 2)->default(0);
            $table->decimal('avg_leader_score', 5, 2)->nullable();
            $table->string('status', 20)->default('draft');
            $table->timestamp('submitted_at')->nullable();
            $table->text('manager_review')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique('assignment_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kpi_team_reports');

        Schema::table('employee_kpis', function (Blueprint $table) {
            $table->dropColumn(['leader_score', 'leader_review']);
        });

        Schema::table('kpi_assignments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('leader_employee_id');
            $table->dropColumn('leader_assigned_at');
        });
    }
};
