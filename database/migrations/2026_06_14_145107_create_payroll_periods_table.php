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
         Schema::create('payroll_periods', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->tinyInteger('month')->unsigned();   // 1 - 12
            $table->year('year');
            $table->enum('status', ['open', 'closed']);
            $table->timestamps();
 
            $table->unique(['month', 'year']);          // Mỗi tháng/năm chỉ có 1 kỳ lương
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_periods');
    }
};
