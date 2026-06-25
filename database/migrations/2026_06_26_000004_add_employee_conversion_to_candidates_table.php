<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            $table->unsignedBigInteger('employee_id')->nullable()->unique()->after('job_post_id');
            $table->timestamp('converted_at')->nullable()->after('status');

            $table->foreign('employee_id')->references('id')->on('employees')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
            $table->dropUnique(['employee_id']);
            $table->dropColumn(['employee_id', 'converted_at']);
        });
    }
};
