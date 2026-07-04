<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->integer('standard_working_days')->default(26)->after('overtime_pay');
            $table->integer('actual_working_days')->default(0)->after('standard_working_days');
        });

        // Set actual_working_days = standard_working_days for existing records
        // so that old data doesn't look broken
        \DB::table('payrolls')->update([
            'actual_working_days' => \DB::raw('standard_working_days'),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropColumn(['standard_working_days', 'actual_working_days']);
        });
    }
};
