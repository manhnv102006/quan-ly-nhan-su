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

        $adminUser = User::query()->where('username', 'admin')->firstOrFail();
        $managerUser = User::query()->where('username', 'manager')->firstOrFail();
        $employeeUser = User::query()->where('username', 'employee')->firstOrFail();

        DB::table('employees')->where('employee_code', 'EMP001')->update(['user_id' => $adminUser->id]);
        DB::table('employees')->where('employee_code', 'EMP002')->update([
            'user_id' => $managerUser->id,
            'email' => $managerUser->email,
        ]);
        DB::table('employees')->where('employee_code', 'EMP004')->update(['user_id' => $employeeUser->id]);

        $emp002Id = DB::table('employees')->where('employee_code', 'EMP002')->value('id');
        DB::table('employees')->where('department_id', 2)->where('id', '!=', $emp002Id)->update(['manager_id' => $emp002Id]);

        DB::table('departments')->where('department_code', 'HR')->update(['manager_id' => 1]);
        DB::table('departments')->where('department_code', 'IT')->update(['manager_id' => $emp002Id]);
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
