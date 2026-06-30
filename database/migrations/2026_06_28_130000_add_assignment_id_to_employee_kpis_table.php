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
        Schema::table('employee_kpis', function (Blueprint $table) {
            $table->foreignId('assignment_id')
                  ->after('id')
                  ->constrained('kpi_assignments')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_kpis', function (Blueprint $table) {
            $table->dropForeign(['assignment_id']);
            $table->dropColumn('assignment_id');
        });
    }
};