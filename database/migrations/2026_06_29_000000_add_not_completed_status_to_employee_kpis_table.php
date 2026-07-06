<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL dùng ENUM; SQLite dùng string (bỏ CHECK cũ) để chấp nhận 'not_completed'.
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE employee_kpis MODIFY status ENUM('pending', 'in_progress', 'completed', 'not_completed') NOT NULL DEFAULT 'pending'");
        } else {
            Schema::table('employee_kpis', function (Blueprint $table) {
                $table->string('status')->default('pending')->change();
            });
        }
    }

    public function down(): void
    {
        DB::table('employee_kpis')
            ->where('status', 'not_completed')
            ->update(['status' => 'pending']);

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE employee_kpis MODIFY status ENUM('pending', 'in_progress', 'completed') NOT NULL DEFAULT 'pending'");
        } else {
            Schema::table('employee_kpis', function (Blueprint $table) {
                $table->string('status')->default('pending')->change();
            });
        }
    }
};
