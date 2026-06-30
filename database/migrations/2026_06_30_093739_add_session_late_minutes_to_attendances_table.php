<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->integer('morning_late_minutes')->default(0)->after('late_minutes');
            $table->integer('afternoon_late_minutes')->default(0)->after('morning_late_minutes');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn(['morning_late_minutes', 'afternoon_late_minutes']);
        });
    }
};