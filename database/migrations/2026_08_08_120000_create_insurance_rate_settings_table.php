<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('insurance_rate_settings')) {
            return;
        }

        Schema::create('insurance_rate_settings', function (Blueprint $table) {
            $table->id();
            $table->decimal('bhxh_employee_rate', 6, 4)->default(0.08);
            $table->decimal('bhxh_employer_rate', 6, 4)->default(0.175);
            $table->decimal('bhyt_employee_rate', 6, 4)->default(0.015);
            $table->decimal('bhyt_employer_rate', 6, 4)->default(0.03);
            $table->decimal('bhtn_employee_rate', 6, 4)->default(0.01);
            $table->decimal('bhtn_employer_rate', 6, 4)->default(0.01);
            $table->text('note')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('insurance_rate_settings');
    }
};
