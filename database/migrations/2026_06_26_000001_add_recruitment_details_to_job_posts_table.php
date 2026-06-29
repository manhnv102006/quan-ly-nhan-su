<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_posts', function (Blueprint $table) {
            $table->unsignedBigInteger('recruiter_id')->nullable()->after('department_id');
            $table->decimal('salary_min', 15, 2)->nullable()->after('quantity');
            $table->decimal('salary_max', 15, 2)->nullable()->after('salary_min');
            $table->string('work_location', 255)->nullable()->after('salary_max');
            $table->enum('work_type', ['full_time', 'part_time', 'remote', 'hybrid', 'contract'])->nullable()->after('work_location');
            $table->date('application_deadline')->nullable()->after('work_type');
            $table->text('requirements')->nullable()->after('description');
            $table->text('benefits')->nullable()->after('requirements');

            $table->foreign('recruiter_id')->references('id')->on('employees')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('job_posts', function (Blueprint $table) {
            $table->dropForeign(['recruiter_id']);
            $table->dropColumn([
                'recruiter_id',
                'salary_min',
                'salary_max',
                'work_location',
                'work_type',
                'application_deadline',
                'requirements',
                'benefits',
            ]);
        });
    }
};
