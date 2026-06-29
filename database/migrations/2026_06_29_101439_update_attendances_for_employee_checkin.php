<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {

            // Số phút đi muộn
            $table->integer('late_minutes')
                ->default(0)
                ->after('work_hours');

            // Có tăng ca hay không
            $table->boolean('is_overtime')
                ->default(false)
                ->after('late_minutes');

            // Số giờ tăng ca
            $table->decimal('overtime_hours', 5, 2)
                ->default(0)
                ->after('is_overtime');

            // Chỉ dùng cho ca hành chính
            $table->dateTime('morning_check_in')->nullable();
            $table->dateTime('morning_check_out')->nullable();

            $table->dateTime('afternoon_check_in')->nullable();
            $table->dateTime('afternoon_check_out')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {

            $table->dropColumn([
                'late_minutes',
                'is_overtime',
                'overtime_hours',

                'morning_check_in',
                'morning_check_out',

                'afternoon_check_in',
                'afternoon_check_out',
            ]);
        });
    }
};