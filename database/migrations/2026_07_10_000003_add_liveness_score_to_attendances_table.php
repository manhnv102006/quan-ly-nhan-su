<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // Điểm liveness (chống giả mạo) của lần nhận diện gần nhất, 0..1.
            $table->decimal('liveness_score', 6, 4)->nullable()->after('recognition_confidence');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn('liveness_score');
        });
    }
};
