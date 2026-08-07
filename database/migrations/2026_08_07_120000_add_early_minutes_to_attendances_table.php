<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->integer('early_minutes')->default(0)->after('afternoon_late_minutes');
            $table->integer('morning_early_minutes')->default(0)->after('early_minutes');
            $table->integer('afternoon_early_minutes')->default(0)->after('morning_early_minutes');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn(['early_minutes', 'morning_early_minutes', 'afternoon_early_minutes']);
        });
    }
};
