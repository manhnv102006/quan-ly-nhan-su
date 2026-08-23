<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_period_bank_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_period_id')->constrained('payroll_periods')->cascadeOnDelete();
            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->string('original_name');
            $table->string('file_path');
            $table->unsignedInteger('file_size')->default(0);
            $table->string('note', 255)->nullable();
            $table->timestamps();

            $table->foreign('uploaded_by')->references('id')->on('users')->nullOnDelete();
            $table->index(['payroll_period_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_period_bank_documents');
    }
};
