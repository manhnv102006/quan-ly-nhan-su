<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("ALTER TABLE leave_request_histories MODIFY COLUMN action ENUM('submitted', 'approved', 'rejected') NOT NULL");
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("ALTER TABLE leave_request_histories MODIFY COLUMN action ENUM('approved', 'rejected') NOT NULL");
    }
};
