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
        Schema::create('candidates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('job_post_id')->nullable();
            $table->string('full_name', 100);
            $table->string('phone', 20);
            $table->string('email', 100);
            $table->string('address', 255);
            $table->date('birth_date')->nullable();
            $table->string('cv_file', 255)->nullable();
            $table->enum('status', ['new', 'interview', 'passed', 'failed']);
            $table->timestamps();
 
            $table->foreign('job_post_id')->references('id')->on('job_posts')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidates');
    }
};
