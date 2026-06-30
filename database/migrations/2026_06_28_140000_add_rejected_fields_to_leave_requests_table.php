<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('leave_requests', 'rejected_by')) {
                $table->unsignedBigInteger('rejected_by')->nullable()->after('reject_reason');
                $table->foreign('rejected_by')->references('id')->on('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('leave_requests', 'rejected_at')) {
                $table->timestamp('rejected_at')->nullable()->after('rejected_by');
            }
        });

        if (Schema::hasColumn('leave_requests', 'rejected_by') && Schema::hasColumn('leave_requests', 'rejected_at')) {
            DB::table('leave_requests')
                ->where('status', 'rejected')
                ->whereNull('rejected_by')
                ->whereNotNull('approved_by')
                ->update([
                    'rejected_by' => DB::raw('approved_by'),
                    'rejected_at' => DB::raw('approved_at'),
                    'approved_by' => null,
                    'approved_at' => null,
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            if (Schema::hasColumn('leave_requests', 'rejected_by')) {
                $table->dropForeign(['rejected_by']);
                $table->dropColumn('rejected_by');
            }

            if (Schema::hasColumn('leave_requests', 'rejected_at')) {
                $table->dropColumn('rejected_at');
            }
        });
    }
};
