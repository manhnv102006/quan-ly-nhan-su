<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('payroll_complaints')) {
            return;
        }

        Schema::create('payroll_complaints', function (Blueprint $table) {
            $table->id();
            $table->string('complaint_code', 30)->unique();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payroll_id')->constrained()->cascadeOnDelete();
            $table->string('issue_type', 40);
            $table->string('subject', 255);
            $table->text('description');
            $table->decimal('disputed_amount', 15, 0)->nullable();
            $table->enum('status', ['pending', 'processing', 'resolved', 'rejected'])->default('pending');
            $table->text('manager_note')->nullable();
            $table->foreignId('manager_confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('manager_confirmed_at')->nullable();
            $table->text('resolution_note')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('rejected_at')->nullable();
            $table->string('reject_reason', 1000)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_complaints');
    }
};
