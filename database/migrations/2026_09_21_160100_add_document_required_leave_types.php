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

        DB::statement("ALTER TABLE leave_requests MODIFY COLUMN leave_type ENUM(
            'annual',
            'sick',
            'maternity',
            'child_sick',
            'wedding',
            'bereavement',
            'compensatory',
            'holiday',
            'business_trip',
            'half_day',
            'unpaid',
            'other'
        ) NOT NULL");
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("ALTER TABLE leave_requests MODIFY COLUMN leave_type ENUM(
            'annual',
            'sick',
            'maternity',
            'compensatory',
            'holiday',
            'business_trip',
            'half_day',
            'unpaid',
            'other'
        ) NOT NULL");
    }
};
