<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recruitment_email_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('candidate_id');
            $table->string('email', 100);
            $table->enum('type', ['interview_result', 'interview_invitation', 'interview_rescheduled', 'interview_reminder']);
            $table->enum('status', ['sent', 'failed']);
            $table->string('subject', 255)->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->foreign('candidate_id')->references('id')->on('candidates')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recruitment_email_logs');
    }
};
