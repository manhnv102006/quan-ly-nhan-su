<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_allowances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_id')->constrained()->cascadeOnDelete();
            $table->foreignId('allowance_type_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name', 100);
            $table->string('code', 50)->nullable();
            $table->decimal('amount', 15, 2)->default(0);
            $table->timestamps();

            $table->index(['payroll_id', 'allowance_type_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_allowances');
    }
};
