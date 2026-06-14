<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('role_id')->nullable(); // ← thêm dòng này
        $table->string('username', 50)->unique();          // ← thêm dòng này
        $table->string('name');
        $table->string('email')->unique();
        $table->timestamp('email_verified_at')->nullable();
        $table->string('password');
        $table->enum('status', ['active', 'inactive']);    // ← thêm dòng này
        $table->rememberToken();
        $table->timestamps();

        // KHÔNG thêm FK role_id ở đây vì roles chưa tồn tại
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
