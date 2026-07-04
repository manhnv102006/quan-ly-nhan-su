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
                $table->unsignedBigInteger('department_id')->nullable()->after('employee_id');
            }
            if (! Schema::hasColumn('contracts', 'position_id')) {
                $table->unsignedBigInteger('position_id')->nullable()->after('department_id');
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
        });

        // Thêm index nếu chưa tồn tại (tránh duplicate key). Dùng Schema::getIndexes
        // để tương thích cả MySQL lẫn SQLite (thay cho "SHOW INDEX" đặc thù MySQL).
        $existingIndexes = collect(Schema::getIndexes('contracts'))->pluck('name')->all();
        if (! in_array('contracts_employee_id_status_index', $existingIndexes)) {
            Schema::table('contracts', function (Blueprint $table) {
                $table->index(['employee_id', 'status'], 'contracts_employee_id_status_index');
            });
        }
        if (! in_array('contracts_start_date_end_date_index', $existingIndexes)) {
            Schema::table('contracts', function (Blueprint $table) {
                $table->index(['start_date', 'end_date'], 'contracts_start_date_end_date_index');
            });
        }

        // backfill department_id và position_id từ employee nếu có.
        // UPDATE ... JOIN chỉ hợp lệ trên MySQL; SQLite (môi trường test) không có
        // dữ liệu để backfill nên bỏ qua an toàn.
        if (DB::getDriverName() === 'mysql') {
            DB::table('contracts')
                ->join('employees', 'employees.id', '=', 'contracts.employee_id')
                ->update([
                    'contracts.department_id' => DB::raw('employees.department_id'),
                    'contracts.position_id' => DB::raw('employees.position_id'),
                ]);
        }

        Schema::table('contracts', function (Blueprint $table) {
            // thêm ràng buộc khóa ngoại cho cột nullable, cho phép set null khi xóa
            $table->foreign('department_id')->references('id')->on('departments')->nullOnDelete();
            $table->foreign('position_id')->references('id')->on('positions')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });

        // Cập nhật trạng thái sang danh sách mới. MySQL dùng ENUM; SQLite dùng string
        // (bỏ ràng buộc CHECK cũ) để cột chấp nhận các giá trị trạng thái mới.
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE contracts MODIFY COLUMN status ENUM('draft','active','expired','cancelled') NOT NULL DEFAULT 'draft'");
        } else {
            Schema::table('contracts', function (Blueprint $table) {
                $table->string('status')->default('draft')->change();
            });
        }
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

            // Xóa index nếu tồn tại
            try { $table->dropIndex('contracts_employee_id_status_index'); } catch (\Throwable $e) {}
            try { $table->dropIndex('contracts_start_date_end_date_index'); } catch (\Throwable $e) {}
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE contracts MODIFY COLUMN status ENUM('active','expired','terminated') NOT NULL");
        } else {
            Schema::table('contracts', function (Blueprint $table) {
                $table->string('status')->change();
            });
        }
    }
};
