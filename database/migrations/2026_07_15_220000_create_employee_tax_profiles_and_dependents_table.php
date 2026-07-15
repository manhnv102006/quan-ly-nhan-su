<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_tax_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('tax_code', 20)->nullable()->comment('Mã số thuế');
            $table->decimal('personal_deduction', 15, 2)->default(11000000);
            $table->text('note')->nullable();
            $table->timestamps();

            $table->unique('employee_id');
        });

        Schema::create('tax_dependents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('full_name');
            $table->string('relationship', 50)->comment('Quan hệ: con, vợ/chồng, cha/mẹ...');
            $table->date('date_of_birth')->nullable();
            $table->string('id_number', 30)->nullable()->comment('CCCD/CMND');
            $table->decimal('monthly_deduction', 15, 2)->default(4400000);
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_dependents');
        Schema::dropIfExists('employee_tax_profiles');
    }
};
