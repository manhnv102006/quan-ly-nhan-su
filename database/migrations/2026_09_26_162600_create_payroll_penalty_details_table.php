<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_penalty_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_id')->constrained()->cascadeOnDelete();
            $table->string('type', 40);
            $table->string('label', 180);
            $table->decimal('amount', 15, 2);
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['payroll_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_penalty_details');
    }
};
