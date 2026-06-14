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
        Schema::create('attendances', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('shift_id');
            $table->date('attendance_date');
            $table->dateTime('check_in')->nullable();
            $table->dateTime('check_out')->nullable();
            $table->decimal('work_hours', 5, 2)->nullable();
            $table->enum('status', ['present', 'late', 'absent', 'leave']);
            $table->timestamps();
 
            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            $table->foreign('shift_id')->references('id')->on('shifts')->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
