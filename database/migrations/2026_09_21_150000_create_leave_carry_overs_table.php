<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_carry_overs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('source_year');
            $table->unsignedSmallInteger('target_year');
            $table->decimal('days', 4, 1);
            $table->decimal('days_used', 4, 1)->default(0);
            $table->date('expires_at');
            $table->enum('status', ['active', 'expired', 'exhausted'])->default('active');
            $table->timestamps();

            $table->unique(
                ['employee_id', 'source_year', 'target_year'],
                'leave_carry_overs_employee_years_unique',
            );
            $table->index(['target_year', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_carry_overs');
    }
};
