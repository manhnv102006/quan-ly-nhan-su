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
            if (! Schema::hasColumn('job_posts', 'position_id')) {
                $table->unsignedBigInteger('position_id')->nullable()->after('department_id');
                $table->foreign('position_id')->references('id')->on('positions')->nullOnDelete();
            }
        });

        $defaultPositionId = DB::table('positions')
            ->where('position_name', 'Nhân viên')
            ->value('id');

        if ($defaultPositionId) {
            DB::table('job_posts')
                ->whereNull('position_id')
                ->update(['position_id' => $defaultPositionId]);
        }
    }

    public function down(): void
    {
        Schema::table('job_posts', function (Blueprint $table) {
            if (Schema::hasColumn('job_posts', 'position_id')) {
                $table->dropForeign(['position_id']);
                $table->dropColumn('position_id');
            }
        });
    }
};
