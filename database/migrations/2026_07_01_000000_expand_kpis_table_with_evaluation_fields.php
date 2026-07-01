<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kpis', function (Blueprint $table) {
            if (! Schema::hasColumn('kpis', 'target')) {
                $table->string('target')->nullable()->after('description');
            }
            if (! Schema::hasColumn('kpis', 'unit')) {
                $table->string('unit', 50)->nullable()->after('target');
            }
            if (! Schema::hasColumn('kpis', 'max_score')) {
                $table->unsignedInteger('max_score')->default(100)->after('weight');
            }
            if (! Schema::hasColumn('kpis', 'period')) {
                $table->enum('period', ['month', 'quarter', 'year'])->nullable()->after('max_score');
            }
            if (! Schema::hasColumn('kpis', 'start_date')) {
                $table->date('start_date')->nullable()->after('period');
            }
            if (! Schema::hasColumn('kpis', 'end_date')) {
                $table->date('end_date')->nullable()->after('start_date');
            }
            if (! Schema::hasColumn('kpis', 'positions')) {
                $table->json('positions')->nullable()->after('end_date');
            }
        });

        if (! Schema::hasTable('kpi_department')) {
            Schema::create('kpi_department', function (Blueprint $table) {
                $table->unsignedBigInteger('kpi_id');
                $table->unsignedBigInteger('department_id');

                $table->foreign('kpi_id')->references('id')->on('kpis')->cascadeOnDelete();
                $table->foreign('department_id')->references('id')->on('departments')->cascadeOnDelete();

                $table->primary(['kpi_id', 'department_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('kpi_department');

        Schema::table('kpis', function (Blueprint $table) {
            foreach (['target', 'unit', 'max_score', 'period', 'start_date', 'end_date', 'positions'] as $column) {
                if (Schema::hasColumn('kpis', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
