<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('early_leave_request_histories');

        Schema::create('early_leave_request_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('early_leave_request_id');
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->string('action', 50);
            $table->text('note')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->foreign('early_leave_request_id', 'elrh_request_fk')
                ->references('id')
                ->on('early_leave_requests')
                ->cascadeOnDelete();

            $table->foreign('actor_id', 'elrh_actor_fk')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->index(['early_leave_request_id', 'action'], 'elrh_request_action_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('early_leave_request_histories');
    }
};
