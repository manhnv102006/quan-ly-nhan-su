<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('team_chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_leader_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('sender_employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('sender_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('type', 20)->default('message');
            $table->string('title')->nullable();
            $table->text('body');
            $table->timestamps();

            $table->index(['team_leader_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('team_chat_messages');
    }
};
