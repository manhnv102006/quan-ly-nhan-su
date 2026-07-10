<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_face_descriptors', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('employee_id');

            // Vector đặc trưng khuôn mặt (512 số) lưu dạng JSON.
            $table->json('embedding');

            // Ảnh mẫu (tuỳ chọn) và điểm chất lượng khi đăng ký.
            $table->string('image_path', 255)->nullable();
            $table->decimal('quality', 6, 4)->nullable();

            // Tên model sinh ra embedding (để sau này đổi model vẫn phân biệt được).
            $table->string('model_name', 50)->default('buffalo_l');

            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            $table->index('employee_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_face_descriptors');
    }
};
