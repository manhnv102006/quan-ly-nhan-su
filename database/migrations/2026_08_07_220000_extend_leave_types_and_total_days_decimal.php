<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'sqlite') {
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

            DB::statement('ALTER TABLE leave_requests MODIFY COLUMN total_days DECIMAL(4,1) NOT NULL');
        } else {
            Schema::table('leave_requests', function (Blueprint $table) {
                $table->decimal('total_days', 4, 1)->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE leave_requests MODIFY COLUMN leave_type ENUM('annual', 'sick', 'unpaid', 'other') NOT NULL");
            DB::statement('ALTER TABLE leave_requests MODIFY COLUMN total_days INT NOT NULL');
        } else {
            Schema::table('leave_requests', function (Blueprint $table) {
                $table->integer('total_days')->change();
            });
        }
    }
};
