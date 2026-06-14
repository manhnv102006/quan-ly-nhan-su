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
         // Bước 1: Tạo bảng roles trước
    Schema::create('roles', function (Blueprint $table) {
        $table->bigIncrements('id');
        $table->string('name', 50);
        $table->string('description', 255)->nullable();
        $table->timestamps();
    });

    // Bước 2: Lúc này roles đã tồn tại → mới thêm FK vào users được
    Schema::table('users', function (Blueprint $table) {
        $table->foreign('role_id')->references('id')->on('roles')->nullOnDelete();
    });
}

public function down(): void
{
    // Xóa FK trước, rồi mới drop bảng roles
    Schema::table('users', function (Blueprint $table) {
        $table->dropForeign(['role_id']);
    });

    Schema::dropIfExists('roles');
}
};