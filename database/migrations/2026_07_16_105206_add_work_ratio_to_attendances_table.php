<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // 1.0 = đủ công, 0.5 = về sớm không phép, null = chưa xác định (backward compat)
            $table->decimal('work_ratio', 3, 2)->default(1.00)->after('work_hours');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn('work_ratio');
        });
    }
};
