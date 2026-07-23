<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RoleSeeder::class);
        $this->call(TaxPolicySeeder::class);

        $adminRoleId = Role::query()->where('name', Role::ADMIN)->value('id');
        $managerRoleId = Role::query()->where('name', Role::MANAGER)->value('id');
        $employeeRoleId = Role::query()->where('name', Role::EMPLOYEE)->value('id');
        $accountantRoleId = Role::query()->where('name', Role::ACCOUNTANT)->value('id');

        $this->seedDemoUser('admin', [
            'name' => 'Quản trị viên',
            'email' => 'admin@example.com',
            'role_id' => $adminRoleId,
        ]);

        $this->seedDemoUser('manager', [
            'name' => 'Trưởng phòng IT',
            'email' => 'manager@example.com',
            'role_id' => $managerRoleId,
        ]);

        $this->seedDemoUser('employee', [
            'name' => 'Nhân viên kinh doanh',
            'email' => 'employee@example.com',
            'role_id' => $employeeRoleId,
        ]);

        $this->seedDemoUser('accountant', [
            'name' => 'Lê Thị Kế Toán',
            'email' => 'accountant@example.com',
            'role_id' => $accountantRoleId,
        ]);

        $this->seedDemoUser('leader', [
            'name' => 'Lê Văn Cường',
            'email' => 'leader@example.com',
            'role_id' => $employeeRoleId,
        ]);

        if (DB::table('positions')->count() === 0) {
            $this->call(PositionSeeder::class);
        }

        if (DB::table('departments')->count() === 0) {
            $this->call(DepartmentSeeder::class);
        }

        if (DB::table('employees')->count() === 0) {
            $this->call(EmployeeSeeder::class);
        }

        $adminUser = User::query()->where('username', 'admin')->firstOrFail();
        $managerUser = User::query()->where('username', 'manager')->firstOrFail();
        $employeeUser = User::query()->where('username', 'employee')->firstOrFail();
        $leaderUser = User::query()->where('username', 'leader')->firstOrFail();

        DB::table('employees')->where('employee_code', 'EMP001')->update(['user_id' => $adminUser->id]);
        DB::table('employees')->where('employee_code', 'EMP002')->update([
            'user_id' => $managerUser->id,
            'email' => $managerUser->email,
        ]);
        DB::table('employees')->where('employee_code', 'EMP003')->update([
            'user_id' => $leaderUser->id,
            'email' => $leaderUser->email,
        ]);
        DB::table('employees')->where('employee_code', 'EMP004')->update(['user_id' => $employeeUser->id]);

        $emp002Id = DB::table('employees')->where('employee_code', 'EMP002')->value('id');
        $emp003Id = DB::table('employees')->where('employee_code', 'EMP003')->value('id');
        DB::table('employees')->where('department_id', 2)->where('id', '!=', $emp002Id)->update(['manager_id' => $emp002Id]);
        DB::table('employees')->whereIn('employee_code', ['EMP004', 'EMP005'])->update(['manager_id' => $emp003Id]);

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

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function seedDemoUser(string $username, array $attributes): User
    {
        return User::query()->updateOrCreate(
            ['username' => $username],
            array_merge([
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'status' => 'active',
            ], $attributes)
        );
    }
}
