<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('module_change_logs', function (Blueprint $table) {
            $table->id();
            $table->string('module', 30);
            $table->string('action', 30);
            $table->string('entity_type', 120);
            $table->unsignedBigInteger('entity_id');
            $table->foreignId('employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->string('field_name', 80)->nullable();
            $table->string('field_label', 150);
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->text('note')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('user_name', 150);
            $table->string('user_role', 50)->nullable();
            $table->timestamps();

            $table->index(['module', 'created_at']);
            $table->index(['employee_id', 'created_at']);
            $table->index(['entity_type', 'entity_id']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_change_logs');
    }
};
