<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RoleSeeder::class);

        $adminRoleId = Role::query()->where('name', Role::ADMIN)->value('id');
        $managerRoleId = Role::query()->where('name', Role::MANAGER)->value('id');
        $employeeRoleId = Role::query()->where('name', Role::EMPLOYEE)->value('id');

        User::factory()->create([
            'username' => 'admin',
            'name' => 'Quản trị viên',
            'email' => 'admin@example.com',
            'status' => 'active',
            'role_id' => $adminRoleId,
        ]);

        User::factory()->create([
            'username' => 'manager',
            'name' => 'Trưởng phòng IT',
            'email' => 'manager@example.com',
            'status' => 'active',
            'role_id' => $managerRoleId,
        ]);

        User::factory()->create([
            'username' => 'employee',
            'name' => 'Nhân viên kinh doanh',
            'email' => 'employee@example.com',
            'status' => 'active',
            'role_id' => $employeeRoleId,
        ]);

        $this->call([
            PositionSeeder::class,
            DepartmentSeeder::class,
            EmployeeSeeder::class,
        ]);

        DB::table('employees')->where('employee_code', 'EMP001')->update(['user_id' => 1]);
        DB::table('employees')->where('employee_code', 'EMP002')->update(['user_id' => 2]);
        DB::table('employees')->where('employee_code', 'EMP004')->update(['user_id' => 3]);

        DB::table('departments')->where('department_code', 'HR')->update(['manager_id' => 1]);
        DB::table('departments')->where('department_code', 'IT')->update(['manager_id' => 2]);
        DB::table('departments')->where('department_code', 'ACC')->update(['manager_id' => 3]);
        DB::table('departments')->where('department_code', 'SALE')->update(['manager_id' => 4]);
        DB::table('departments')->where('department_code', 'MKT')->update(['manager_id' => 5]);

        $this->call([
            ContractTypeSeeder::class,
            ShiftSeeder::class,
            PayrollPeriodSeeder::class,
            ContractSeeder::class,
            AttendanceSeeder::class,
            LeaveRequestSeeder::class,
            KPISeeder::class,
            EmployeeDocumentSeeder::class,
            JobPostSeeder::class,
            PayrollSeeder::class,
            EmployeeKPISeeder::class,
            CandidateSeeder::class,
            NotificationSeeder::class,
            InterviewSeeder::class,
            NotificationUserSeeder::class,
        ]);
    }
}
