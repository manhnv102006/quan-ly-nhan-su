<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE candidates MODIFY status ENUM('new', 'interview', 'pending_hire_approval', 'passed', 'failed') NOT NULL DEFAULT 'new'");
        }
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE candidates MODIFY status ENUM('new', 'interview', 'passed', 'failed') NOT NULL DEFAULT 'new'");
        }
    }
};
