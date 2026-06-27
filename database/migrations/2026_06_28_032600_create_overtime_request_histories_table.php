<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('overtime_request_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('overtime_request_id');
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->string('action', 50);
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->foreign('overtime_request_id')
                ->references('id')
                ->on('overtime_requests')
                ->cascadeOnDelete();

            $table->foreign('actor_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->index(['overtime_request_id', 'action'], 'overtime_hist_request_action_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('overtime_request_histories');
    }
};
