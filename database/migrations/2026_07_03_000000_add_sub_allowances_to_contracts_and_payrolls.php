<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->decimal('allowance_meal', 15, 2)->default(0)->after('salary');
            $table->decimal('allowance_phone', 15, 2)->default(0)->after('allowance_meal');
            $table->decimal('allowance_fuel', 15, 2)->default(0)->after('allowance_phone');
        });

        Schema::table('payrolls', function (Blueprint $table) {
            $table->decimal('allowance_meal', 15, 2)->default(0)->after('basic_salary');
            $table->decimal('allowance_phone', 15, 2)->default(0)->after('allowance_meal');
            $table->decimal('allowance_fuel', 15, 2)->default(0)->after('allowance_phone');
        });
    }

    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn(['allowance_meal', 'allowance_phone', 'allowance_fuel']);
        });

        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropColumn(['allowance_meal', 'allowance_phone', 'allowance_fuel']);
        });
    }
};
