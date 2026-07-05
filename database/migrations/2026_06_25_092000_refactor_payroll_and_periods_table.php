<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Alter payroll_periods table
        Schema::table('payroll_periods', function (Blueprint $table) {
            // Add approval/payment tracking columns
            $table->unsignedBigInteger('approved_by')->nullable()->after('status');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->unsignedBigInteger('paid_by')->nullable()->after('approved_at');
            $table->timestamp('paid_at')->nullable()->after('paid_by');

            $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('paid_by')->references('id')->on('users')->nullOnDelete();
        });

        // Mở rộng status. MySQL dùng ENUM; SQLite dùng string (bỏ CHECK cũ) để chấp nhận
        // các trạng thái mới. Không dùng try/catch để tránh nuốt lỗi thật trên MySQL.
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE payroll_periods MODIFY COLUMN status ENUM('open', 'calculated', 'approved', 'paid', 'closed') DEFAULT 'open'");
        } else {
            Schema::table('payroll_periods', function (Blueprint $table) {
                $table->string('status')->default('open')->change();
            });
        }

        // 2. Alter payrolls table (drop columns no longer needed as they are moved to periods)
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropForeign(['paid_by']);
            
            $table->dropColumn(['status', 'approved_by', 'approved_at', 'paid_by', 'paid_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore payrolls columns
        Schema::table('payrolls', function (Blueprint $table) {
            $table->enum('status', ['draft', 'pending', 'approved', 'paid'])->default('draft')->after('total_salary');
            $table->unsignedBigInteger('approved_by')->nullable()->after('status');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->unsignedBigInteger('paid_by')->nullable()->after('approved_at');
            $table->timestamp('paid_at')->nullable()->after('paid_by');

            $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('paid_by')->references('id')->on('users')->nullOnDelete();
        });

        // Restore payroll_periods status enum and drop tracking columns
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE payroll_periods MODIFY COLUMN status ENUM('open', 'closed') DEFAULT 'open'");
        } else {
            Schema::table('payroll_periods', function (Blueprint $table) {
                $table->string('status')->default('open')->change();
            });
        }

        Schema::table('payroll_periods', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropForeign(['paid_by']);
            $table->dropColumn(['approved_by', 'approved_at', 'paid_by', 'paid_at']);
        });
    }
};
