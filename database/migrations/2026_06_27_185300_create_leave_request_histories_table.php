<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_request_histories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('leave_request_id');
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->enum('action', ['approved', 'rejected']);
            $table->text('note')->nullable();
            $table->timestamps();

            $table->foreign('leave_request_id')->references('id')->on('leave_requests')->cascadeOnDelete();
            $table->foreign('actor_id')->references('id')->on('users')->nullOnDelete();
            $table->index(['leave_request_id', 'action']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_request_histories');
    }
};
