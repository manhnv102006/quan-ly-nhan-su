<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_insurances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('social_insurance_number', 50)->nullable()->comment('Số sổ BHXH');
            $table->string('health_insurance_code', 50)->nullable()->comment('Mã BHYT');
            $table->decimal('contribution_salary', 15, 2)->default(0)->comment('Mức lương đóng BH');
            $table->decimal('bhxh_employee_rate', 6, 4)->default(0.08);
            $table->decimal('bhxh_employer_rate', 6, 4)->default(0.175);
            $table->decimal('bhyt_employee_rate', 6, 4)->default(0.015);
            $table->decimal('bhyt_employer_rate', 6, 4)->default(0.03);
            $table->decimal('bhtn_employee_rate', 6, 4)->default(0.01);
            $table->decimal('bhtn_employer_rate', 6, 4)->default(0.01);
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->enum('status', ['active', 'suspended', 'stopped'])->default('active');
            $table->string('stop_reason')->nullable();
            $table->foreignId('managed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->unique('employee_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_insurances');
    }
};
