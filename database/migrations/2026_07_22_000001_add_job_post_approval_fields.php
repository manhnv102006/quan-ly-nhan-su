<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_posts', function (Blueprint $table) {
            $table->unsignedBigInteger('submitted_by_employee_id')->nullable()->after('recruiter_id');
            $table->foreign('submitted_by_employee_id')->references('id')->on('employees')->nullOnDelete();
        });

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE job_posts MODIFY status ENUM('open', 'closed', 'pending_approval', 'rejected') NOT NULL DEFAULT 'open'");
        }
    }

    public function down(): void
    {
        Schema::table('job_posts', function (Blueprint $table) {
            $table->dropForeign(['submitted_by_employee_id']);
            $table->dropColumn('submitted_by_employee_id');
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE job_posts MODIFY status ENUM('open', 'closed') NOT NULL DEFAULT 'open'");
        }
    }
};
