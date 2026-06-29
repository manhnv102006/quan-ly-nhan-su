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
        Schema::create('employee_kpis', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('kpi_id');
            $table->unsignedBigInteger('assigned_by')->nullable();
            $table->integer('progress')->default(0);
            $table->decimal('score', 5, 2)->nullable();
            $table->text('comment')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed', 'not_completed'])->default('pending');
            $table->timestamps();
 
            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            $table->foreign('kpi_id')->references('id')->on('kpis')->cascadeOnDelete();
            $table->foreign('assigned_by')->references('id')->on('employees')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_kpis');
    }
};
