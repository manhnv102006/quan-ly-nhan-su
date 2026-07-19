<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $leaderRoleId = DB::table('roles')->where('name', 'leader')->value('id');
        $employeeRoleId = DB::table('roles')->where('name', 'employee')->value('id');

        if (! $leaderRoleId || ! $employeeRoleId) {
            return;
        }

        DB::table('users')
            ->where('role_id', $leaderRoleId)
            ->update(['role_id' => $employeeRoleId]);

        DB::table('roles')->where('id', $leaderRoleId)->delete();
    }

    public function down(): void
    {
        $leaderRoleId = DB::table('roles')->insertGetId([
            'name' => 'leader',
            'description' => 'Trưởng nhóm - quản lý nhân viên, KPI và công việc nhóm',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $employeeRoleId = DB::table('roles')->where('name', 'employee')->value('id');

        if (! $employeeRoleId) {
            return;
        }

        DB::table('users')
            ->where('username', 'leader')
            ->update(['role_id' => $leaderRoleId]);
    }
};
