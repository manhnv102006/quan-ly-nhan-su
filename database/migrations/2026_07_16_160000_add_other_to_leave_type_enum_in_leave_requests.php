<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE leave_requests MODIFY COLUMN leave_type ENUM('annual', 'sick', 'unpaid', 'other') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE leave_requests MODIFY COLUMN leave_type ENUM('annual', 'sick', 'unpaid') NOT NULL");
    }
};
