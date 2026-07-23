<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tax_policies', function (Blueprint $table) {
            $table->id();
            $table->string('code', 64)->unique();
            $table->string('name');
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->decimal('personal_deduction', 15, 2);
            $table->decimal('dependent_deduction_default', 15, 2);
            $table->json('brackets');
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['effective_from', 'effective_to']);
        });

        Schema::create('payroll_tax_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_id')->unique()->constrained('payrolls')->cascadeOnDelete();
            $table->foreignId('tax_policy_id')->nullable()->constrained('tax_policies')->nullOnDelete();
            $table->string('policy_code', 64)->nullable();
            $table->string('policy_label')->nullable();
            $table->unsignedTinyInteger('dependents_count')->default(0);
            $table->decimal('personal_deduction', 15, 2)->default(0);
            $table->decimal('dependent_deduction', 15, 2)->default(0);
            $table->decimal('gross_income', 15, 2)->default(0);
            $table->decimal('insurance_employee', 15, 2)->default(0);
            $table->decimal('assessable_income', 15, 2)->default(0);
            $table->decimal('taxable_income', 15, 2)->default(0);
            $table->decimal('pit', 15, 2)->default(0);
            $table->decimal('net_income', 15, 2)->default(0);
            $table->json('brackets_snapshot')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_tax_snapshots');
        Schema::dropIfExists('tax_policies');
    }
};
