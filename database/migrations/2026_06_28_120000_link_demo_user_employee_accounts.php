<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $links = [
            'admin' => 'EMP001',
            'manager' => 'EMP002',
            'employee' => 'EMP004',
        ];

        foreach ($links as $username => $employeeCode) {
            $userId = DB::table('users')->where('username', $username)->value('id');
            if (! $userId) {
                continue;
            }

            DB::table('employees')
                ->where('employee_code', $employeeCode)
                ->where(function ($query) use ($userId) {
                    $query->whereNull('user_id')->orWhere('user_id', $userId);
                })
                ->update(['user_id' => $userId]);
        }

        $managerEmployeeId = DB::table('employees')->where('employee_code', 'EMP002')->value('id');
        if ($managerEmployeeId) {
            DB::table('departments')->where('department_code', 'IT')->update(['manager_id' => $managerEmployeeId]);
        }
    }

    public function down(): void
    {
        // Không rollback liên kết dữ liệu nghiệp vụ.
    }
};
