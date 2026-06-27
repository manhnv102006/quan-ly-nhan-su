<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Đổi tên cột overtime_date -> work_date
        if (Schema::hasColumn('overtime_requests', 'overtime_date') && ! Schema::hasColumn('overtime_requests', 'work_date')) {
            DB::statement('ALTER TABLE overtime_requests CHANGE overtime_date work_date DATE NOT NULL');
        }

        Schema::table('overtime_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('overtime_requests', 'work_date')) {
                $table->date('work_date')->after('employee_id');
            }

            if (! Schema::hasColumn('overtime_requests', 'total_hours')) {
                $table->decimal('total_hours', 5, 2)->default(0)->after('end_time');
            }

            if (! Schema::hasColumn('overtime_requests', 'approved_by')) {
                $table->unsignedBigInteger('approved_by')->nullable()->after('status');
            }

            if (! Schema::hasColumn('overtime_requests', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approved_by');
            }

            if (! Schema::hasColumn('overtime_requests', 'reject_reason')) {
                $table->text('reject_reason')->nullable()->after('approved_at');
            }
        });

        // Chuyển dữ liệu manager_note cũ qua reject_reason trước khi drop
        if (Schema::hasColumn('overtime_requests', 'manager_note') && Schema::hasColumn('overtime_requests', 'reject_reason')) {
            DB::statement('UPDATE overtime_requests SET reject_reason = manager_note WHERE reject_reason IS NULL AND manager_note IS NOT NULL');
        }

        // Bỏ cột không còn dùng
        Schema::table('overtime_requests', function (Blueprint $table) {
            if (Schema::hasColumn('overtime_requests', 'overtime_type')) {
                $table->dropColumn('overtime_type');
            }
            if (Schema::hasColumn('overtime_requests', 'manager_note')) {
                $table->dropColumn('manager_note');
            }
        });

        // Mở rộng status
        DB::statement("ALTER TABLE overtime_requests MODIFY COLUMN status ENUM('pending','approved','rejected','completed') NOT NULL DEFAULT 'pending'");

        // Chuẩn hóa khóa ngoại approved_by -> users.id (nếu đã tồn tại khóa cũ sẽ drop trước)
        $foreignKeys = collect(DB::select("
            SELECT CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'overtime_requests'
              AND COLUMN_NAME = 'approved_by'
              AND REFERENCED_TABLE_NAME IS NOT NULL
        "))->pluck('CONSTRAINT_NAME')->toArray();

        foreach ($foreignKeys as $foreignKey) {
            DB::statement("ALTER TABLE overtime_requests DROP FOREIGN KEY `{$foreignKey}`");
        }

        Schema::table('overtime_requests', function (Blueprint $table) {
            $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE overtime_requests MODIFY COLUMN status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending'");

        if (Schema::hasColumn('overtime_requests', 'work_date') && ! Schema::hasColumn('overtime_requests', 'overtime_date')) {
            DB::statement('ALTER TABLE overtime_requests CHANGE work_date overtime_date DATE NOT NULL');
        }

        Schema::table('overtime_requests', function (Blueprint $table) {
            if (Schema::hasColumn('overtime_requests', 'reject_reason')) {
                $table->dropColumn('reject_reason');
            }
            if (Schema::hasColumn('overtime_requests', 'approved_at')) {
                $table->dropColumn('approved_at');
            }
            if (Schema::hasColumn('overtime_requests', 'approved_by')) {
                try {
                    $table->dropForeign(['approved_by']);
                } catch (\Throwable $e) {
                    // ignore khi không có foreign key
                }
                $table->dropColumn('approved_by');
            }
        });
    }
};
