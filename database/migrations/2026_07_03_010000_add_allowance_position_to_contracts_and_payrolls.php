<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->decimal('allowance_position', 15, 2)->default(0)->after('allowance_fuel');
        });

        Schema::table('payrolls', function (Blueprint $table) {
            $table->decimal('allowance_position', 15, 2)->default(0)->after('allowance_fuel');
        });
    }

    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn('allowance_position');
        });

        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropColumn('allowance_position');
        });
    }
};
