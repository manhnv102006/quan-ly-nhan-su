<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
         Schema::create('notifications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title', 255);
            $table->text('content');
            $table->unsignedBigInteger('sender_id')->nullable();
            $table->enum('type', ['system', 'leave', 'payroll', 'kpi'])->default('system');
            $table->timestamps();
 
            $table->foreign('sender_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
