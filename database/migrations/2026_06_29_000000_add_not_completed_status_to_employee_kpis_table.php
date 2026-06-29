<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE employee_kpis MODIFY status ENUM('pending', 'in_progress', 'completed', 'not_completed') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        DB::table('employee_kpis')
            ->where('status', 'not_completed')
            ->update(['status' => 'pending']);

        DB::statement("ALTER TABLE employee_kpis MODIFY status ENUM('pending', 'in_progress', 'completed') NOT NULL DEFAULT 'pending'");
    }
};
