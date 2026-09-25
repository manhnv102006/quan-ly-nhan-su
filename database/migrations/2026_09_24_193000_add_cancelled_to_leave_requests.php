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

        DB::statement("ALTER TABLE leave_requests MODIFY COLUMN status ENUM('pending', 'approved', 'rejected', 'cancelled') NOT NULL DEFAULT 'pending'");
        DB::statement("ALTER TABLE leave_request_histories MODIFY COLUMN action ENUM('submitted', 'approved', 'rejected', 'cancelled') NOT NULL");
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return;
        }

        DB::table('leave_requests')->where('status', 'cancelled')->update(['status' => 'rejected']);
        DB::table('leave_request_histories')->where('action', 'cancelled')->update(['action' => 'rejected']);

        DB::statement("ALTER TABLE leave_requests MODIFY COLUMN status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending'");
        DB::statement("ALTER TABLE leave_request_histories MODIFY COLUMN action ENUM('submitted', 'approved', 'rejected') NOT NULL");
    }
};
