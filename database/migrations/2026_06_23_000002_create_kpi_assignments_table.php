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
        Schema::create('kpi_assignments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('kpi_id');
            $table->unsignedBigInteger('manager_id');
            $table->decimal('target', 10, 2)->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->text('note')->nullable();
            $table->enum('status', ['pending', 'active', 'completed', 'cancelled'])->default('pending');
            $table->unsignedBigInteger('assigned_by');
            $table->timestamps();

            $table->foreign('kpi_id')->references('id')->on('kpis')->cascadeOnDelete();
            $table->foreign('manager_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('assigned_by')->references('id')->on('users')->cascadeOnDelete();

            $table->index(['kpi_id', 'manager_id']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kpi_assignments');
    }
};
