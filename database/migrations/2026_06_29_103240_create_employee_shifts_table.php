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
    Schema::create('employee_shifts', function (Blueprint $table) {

        $table->id();

        $table->foreignId('employee_id')
            ->constrained()
            ->cascadeOnDelete();

        $table->foreignId('shift_id')
            ->constrained()
            ->cascadeOnDelete();

        $table->date('work_date');

        $table->timestamps();

    });
}

public function down(): void
{
    Schema::dropIfExists('employee_shifts');
}
};
