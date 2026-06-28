<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('interviews', function (Blueprint $table) {
            $table->enum('status', ['scheduled', 'completed', 'cancelled', 'no_show'])->default('scheduled')->after('interview_date');
            $table->unsignedTinyInteger('technical_score')->nullable()->after('result');
            $table->unsignedTinyInteger('attitude_score')->nullable()->after('technical_score');
            $table->unsignedTinyInteger('culture_score')->nullable()->after('attitude_score');
            $table->unsignedTinyInteger('overall_score')->nullable()->after('culture_score');
            $table->enum('recommendation', ['hire', 'consider', 'reject'])->nullable()->after('overall_score');
            $table->text('strengths')->nullable()->after('recommendation');
            $table->text('weaknesses')->nullable()->after('strengths');
        });
    }

    public function down(): void
    {
        Schema::table('interviews', function (Blueprint $table) {
            $table->dropColumn([
                'status',
                'technical_score',
                'attitude_score',
                'culture_score',
                'overall_score',
                'recommendation',
                'strengths',
                'weaknesses',
            ]);
        });
    }
};
