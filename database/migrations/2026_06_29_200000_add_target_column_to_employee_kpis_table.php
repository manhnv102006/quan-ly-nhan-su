<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_kpis', function (Blueprint $table) {
            if (!Schema::hasColumn('employee_kpis', 'target')) {
                $table->string('target')->nullable()->after('employee_id');
            }

            // đảm bảo comment/score tồn tại nếu form/code đang sử dụng
            if (!Schema::hasColumn('employee_kpis', 'comment')) {
                $table->text('comment')->nullable()->after('target');
            }

            if (!Schema::hasColumn('employee_kpis', 'score')) {
                $table->decimal('score', 5, 2)->nullable()->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('employee_kpis', function (Blueprint $table) {
            if (Schema::hasColumn('employee_kpis', 'target')) {
                $table->dropColumn('target');
            }
            // không tự drop comment/score trong down để tránh phá dữ liệu nếu cột đã tồn tại ở các môi trường khác
        });
    }
};

