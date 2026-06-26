<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            if (! Schema::hasColumn('contracts', 'department_id')) {
                $table->unsignedBigInteger('department_id')->after('employee_id');
            }
            if (! Schema::hasColumn('contracts', 'position_id')) {
                $table->unsignedBigInteger('position_id')->after('department_id');
            }
            if (! Schema::hasColumn('contracts', 'allowance')) {
                $table->decimal('allowance', 15, 2)->default(0)->after('salary');
            }
            if (! Schema::hasColumn('contracts', 'description')) {
                $table->text('description')->nullable()->after('allowance');
            }
            if (! Schema::hasColumn('contracts', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('note');
            }

            $table->index(['employee_id', 'status']);
            $table->index(['start_date', 'end_date']);

            $table->foreign('department_id')->references('id')->on('departments')->restrictOnDelete();
            $table->foreign('position_id')->references('id')->on('positions')->restrictOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });

        // Cập nhật trạng thái sang danh sách mới
        DB::statement("ALTER TABLE contracts MODIFY COLUMN status ENUM('draft','active','expired','cancelled') NOT NULL DEFAULT 'draft'");
    }

    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            if (Schema::hasColumn('contracts', 'created_by')) {
                $table->dropForeign(['created_by']);
                $table->dropColumn('created_by');
            }
            if (Schema::hasColumn('contracts', 'description')) {
                $table->dropColumn('description');
            }
            if (Schema::hasColumn('contracts', 'allowance')) {
                $table->dropColumn('allowance');
            }
            if (Schema::hasColumn('contracts', 'position_id')) {
                $table->dropForeign(['position_id']);
                $table->dropColumn('position_id');
            }
            if (Schema::hasColumn('contracts', 'department_id')) {
                $table->dropForeign(['department_id']);
                $table->dropColumn('department_id');
            }

            $table->dropIndex(['employee_id', 'status']);
            $table->dropIndex(['start_date', 'end_date']);
        });

        DB::statement("ALTER TABLE contracts MODIFY COLUMN status ENUM('active','expired','terminated') NOT NULL");
    }
};
