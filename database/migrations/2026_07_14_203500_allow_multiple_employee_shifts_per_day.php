<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_shifts', function (Blueprint $table) {
            $table->index('employee_id', 'employee_shifts_employee_id_index');
            $table->dropUnique('employee_shifts_employee_date_unique');
            $table->unique(
                ['employee_id', 'work_date', 'shift_id'],
                'employee_shifts_employee_date_shift_unique',
            );
        });
    }

    public function down(): void
    {
        Schema::table('employee_shifts', function (Blueprint $table) {
            $table->dropUnique('employee_shifts_employee_date_shift_unique');
            $table->unique(['employee_id', 'work_date'], 'employee_shifts_employee_date_unique');
            $table->dropIndex('employee_shifts_employee_id_index');
        });
    }
};
