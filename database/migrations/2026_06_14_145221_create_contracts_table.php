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
        Schema::create('contracts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('contract_type_id');
            $table->string('contract_code', 50)->unique();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->decimal('salary', 15, 2);
            $table->enum('status', ['active', 'expired', 'terminated']);
            $table->string('file_path', 255)->nullable();
            $table->date('signed_date')->nullable();
            $table->timestamps();
 
            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            $table->foreign('contract_type_id')->references('id')->on('contract_types')->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
