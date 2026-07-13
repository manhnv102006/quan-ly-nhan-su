<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // Phương thức chấm công cho mỗi lượt vào/ra: thủ công hoặc bằng khuôn mặt.
            $table->enum('check_in_method', ['manual', 'face'])->default('manual')->after('status');
            $table->enum('check_out_method', ['manual', 'face'])->default('manual')->after('check_in_method');

            // Độ tương đồng khuôn mặt (cosine) của lần nhận diện gần nhất.
            $table->decimal('recognition_confidence', 6, 4)->nullable()->after('check_out_method');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn([
                'check_in_method',
                'check_out_method',
                'recognition_confidence',
            ]);
        });
    }
};
