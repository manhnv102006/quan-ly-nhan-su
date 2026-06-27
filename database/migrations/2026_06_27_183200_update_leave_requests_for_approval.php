<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            // Trạng thái duyệt
            if (! Schema::hasColumn('leave_requests', 'status')) {
                $table->enum('status', ['pending', 'approved', 'rejected'])
                    ->default('pending')
                    ->after('reason');
            }

            // Người duyệt
            if (! Schema::hasColumn('leave_requests', 'approved_by')) {
                $table->unsignedBigInteger('approved_by')->nullable()->after('status');
                $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
            }

            // Thời gian duyệt
            if (! Schema::hasColumn('leave_requests', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approved_by');
            }

            // Lý do từ chối
            if (! Schema::hasColumn('leave_requests', 'reject_reason')) {
                $table->text('reject_reason')->nullable()->after('approved_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            if (Schema::hasColumn('leave_requests', 'reject_reason')) {
                $table->dropColumn('reject_reason');
            }
            if (Schema::hasColumn('leave_requests', 'approved_at')) {
                $table->dropColumn('approved_at');
            }
            if (Schema::hasColumn('leave_requests', 'approved_by')) {
                $table->dropForeign(['approved_by']);
                $table->dropColumn('approved_by');
            }
            if (Schema::hasColumn('leave_requests', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
