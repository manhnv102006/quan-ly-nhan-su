<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('leave_requests', 'leader_approved_by')) {
                $table->unsignedBigInteger('leader_approved_by')->nullable()->after('status');
                $table->foreign('leader_approved_by')->references('id')->on('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('leave_requests', 'leader_approved_at')) {
                $table->timestamp('leader_approved_at')->nullable()->after('leader_approved_by');
            }
        });

        Schema::table('overtime_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('overtime_requests', 'leader_approved_by')) {
                $table->unsignedBigInteger('leader_approved_by')->nullable()->after('status');
                $table->foreign('leader_approved_by')->references('id')->on('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('overtime_requests', 'leader_approved_at')) {
                $table->timestamp('leader_approved_at')->nullable()->after('leader_approved_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            if (Schema::hasColumn('leave_requests', 'leader_approved_by')) {
                $table->dropForeign(['leader_approved_by']);
                $table->dropColumn('leader_approved_by');
            }

            if (Schema::hasColumn('leave_requests', 'leader_approved_at')) {
                $table->dropColumn('leader_approved_at');
            }
        });

        Schema::table('overtime_requests', function (Blueprint $table) {
            if (Schema::hasColumn('overtime_requests', 'leader_approved_by')) {
                $table->dropForeign(['leader_approved_by']);
                $table->dropColumn('leader_approved_by');
            }

            if (Schema::hasColumn('overtime_requests', 'leader_approved_at')) {
                $table->dropColumn('leader_approved_at');
            }
        });
    }
};
