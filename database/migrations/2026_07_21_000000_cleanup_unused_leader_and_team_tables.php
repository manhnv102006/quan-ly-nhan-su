<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Gỡ cột leader trên leave_requests / overtime_requests
        foreach (['leave_requests', 'overtime_requests'] as $table) {
            Schema::table($table, function (Blueprint $blueprint) use ($table) {
                if (Schema::hasColumn($table, 'leader_approved_by')) {
                    $blueprint->dropForeign(['leader_approved_by']);
                    $blueprint->dropColumn('leader_approved_by');
                }

                if (Schema::hasColumn($table, 'leader_approved_at')) {
                    $blueprint->dropColumn('leader_approved_at');
                }
            });
        }

        // 2. Gỡ cột leader trên kpi_assignments
        Schema::table('kpi_assignments', function (Blueprint $table) {
            if (Schema::hasColumn('kpi_assignments', 'leader_employee_id')) {
                $table->dropForeign(['leader_employee_id']);
                $table->dropColumn('leader_employee_id');
            }

            if (Schema::hasColumn('kpi_assignments', 'leader_assigned_at')) {
                $table->dropColumn('leader_assigned_at');
            }
        });

        // 3. Gỡ cột leader trên employee_kpis
        Schema::table('employee_kpis', function (Blueprint $table) {
            if (Schema::hasColumn('employee_kpis', 'leader_score')) {
                $table->dropColumn('leader_score');
            }

            if (Schema::hasColumn('employee_kpis', 'leader_review')) {
                $table->dropColumn('leader_review');
            }
        });

        // 4. Xóa các bảng không còn dùng (đã gỡ tính năng leader/nhóm/chat + log hợp đồng cũ)
        Schema::dropIfExists('kpi_team_reports');
        Schema::dropIfExists('leader_team_reports');
        Schema::dropIfExists('team_chat_messages');
        Schema::dropIfExists('team_membership_requests');
        Schema::dropIfExists('teams');
        Schema::dropIfExists('contract_activity_logs');
    }

    public function down(): void
    {
        // Chỉ khôi phục lại các cột (dữ liệu cũ không thể phục hồi).
        // Các bảng tính năng đã gỡ không tạo lại vì mã nguồn không còn tham chiếu.
        foreach (['leave_requests', 'overtime_requests'] as $table) {
            Schema::table($table, function (Blueprint $blueprint) use ($table) {
                if (! Schema::hasColumn($table, 'leader_approved_by')) {
                    $blueprint->unsignedBigInteger('leader_approved_by')->nullable()->after('status');
                    $blueprint->foreign('leader_approved_by')->references('id')->on('users')->nullOnDelete();
                }

                if (! Schema::hasColumn($table, 'leader_approved_at')) {
                    $blueprint->timestamp('leader_approved_at')->nullable()->after('leader_approved_by');
                }
            });
        }

        Schema::table('kpi_assignments', function (Blueprint $table) {
            if (! Schema::hasColumn('kpi_assignments', 'leader_employee_id')) {
                $table->foreignId('leader_employee_id')->nullable()->after('assigned_by')->constrained('employees')->nullOnDelete();
            }

            if (! Schema::hasColumn('kpi_assignments', 'leader_assigned_at')) {
                $table->timestamp('leader_assigned_at')->nullable()->after('leader_employee_id');
            }
        });

        Schema::table('employee_kpis', function (Blueprint $table) {
            if (! Schema::hasColumn('employee_kpis', 'leader_score')) {
                $table->decimal('leader_score', 5, 2)->nullable()->after('score');
            }

            if (! Schema::hasColumn('employee_kpis', 'leader_review')) {
                $table->text('leader_review')->nullable()->after('leader_score');
            }
        });
    }
};
