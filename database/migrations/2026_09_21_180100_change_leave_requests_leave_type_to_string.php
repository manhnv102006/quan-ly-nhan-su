<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Loại nghỉ phép giờ do Admin quản lý trong bảng leave_types nên cột không thể
     * là ENUM cố định nữa — mỗi loại mới sẽ cần một migration ALTER TABLE.
     */
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE leave_requests MODIFY COLUMN leave_type VARCHAR(50) NOT NULL');
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
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
};
