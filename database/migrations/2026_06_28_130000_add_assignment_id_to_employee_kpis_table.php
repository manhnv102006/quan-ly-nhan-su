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
            if (! Schema::hasColumn('employee_kpis', 'assignment_id')) {
                $table->foreignId('assignment_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('kpi_assignments')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('employee_kpis', function (Blueprint $table) {
            if (Schema::hasColumn('employee_kpis', 'assignment_id')) {
                $table->dropForeign(['assignment_id']);
                $table->dropColumn('assignment_id');
            }
        });
    }
};