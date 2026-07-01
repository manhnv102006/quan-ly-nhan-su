<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Dữ liệu demo để test Manager duyệt nghỉ phép, Admin hợp đồng, v.v.
 * Chạy: php artisan db:seed --class=DemoTestDataSeeder
 */
class DemoTestDataSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $this->ensureDemoUsers();
            $managerEmployee = $this->linkManagerAccounts();
            $subordinates = $this->ensureItTeam($managerEmployee);
            $this->seedLeaveRequests($managerEmployee, $subordinates);
            $this->seedOvertimeRequests($managerEmployee);
            $this->ensureContractLinks();
        });

        $this->command?->info('Demo test data seeded successfully.');
        $this->command?->table(
            ['Vai trò', 'Username', 'Mật khẩu', 'Ghi chú'],
            [
                ['Admin', 'admin', 'password', 'EMP001 · /admin/*'],
                ['Manager', 'manager', 'password', 'EMP002 · duyệt nghỉ phép IT'],
                ['Manager', 'anhlethanh', 'password', 'Liên kết EMP002 nếu có tài khoản'],
                ['Employee', 'employee', 'password', 'EMP004 · tạo đơn nghỉ'],
                ['Employee', 'nvit01', 'password', 'EMP006 · cấp dưới IT'],
            ]
        );
    }

    private function ensureDemoUsers(): void
    {
        $managerRoleId = Role::query()->where('name', Role::MANAGER)->value('id');
        $employeeRoleId = Role::query()->where('name', Role::EMPLOYEE)->value('id');

        $defaults = [
            [
                'username' => 'anhlethanh',
                'name' => 'Anh Lê Thành',
                'email' => 'anhlethanh@example.com',
                'role_id' => $managerRoleId,
            ],
            [
                'username' => 'nvit01',
                'name' => 'Nguyễn Văn IT',
                'email' => 'nvit01@example.com',
                'role_id' => $employeeRoleId,
            ],
        ];

        foreach ($defaults as $data) {
            User::query()->firstOrCreate(
                ['username' => $data['username']],
                [
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'password' => Hash::make('password'),
                    'status' => 'active',
                    'role_id' => $data['role_id'],
                ]
            );
        }
    }

    private function linkManagerAccounts(): Employee
    {
        $itDepartment = Department::query()->where('department_code', 'IT')->firstOrFail();
        $managerEmployee = Employee::query()->where('employee_code', 'EMP002')->firstOrFail();

        $itDepartment->update(['manager_id' => $managerEmployee->id]);
        $managerEmployee->update(['department_id' => $itDepartment->id]);

        $linkedUser = User::query()->where('username', 'anhlethanh')->first()
            ?? User::query()->where('username', 'manager')->first();

        if ($linkedUser) {
            Employee::query()
                ->where('user_id', $linkedUser->id)
                ->where('id', '!=', $managerEmployee->id)
                ->update(['user_id' => null]);

            $managerEmployee->update([
                'user_id' => $linkedUser->id,
                'email' => $linkedUser->email,
            ]);
        }

        return $managerEmployee->fresh();
    }

    /**
     * @return list<Employee>
     */
    private function ensureItTeam(Employee $manager): array
    {
        $itDepartment = Department::query()->where('department_code', 'IT')->firstOrFail();
        $positionId = (int) (DB::table('positions')->where('position_name', 'Nhân viên')->value('id') ?? 4);

        $team = [
            [
                'employee_code' => 'EMP006',
                'full_name' => 'Phạm Minh Đức',
                'email' => 'duc@example.com',
                'phone' => '0901000006',
                'gender' => 'male',
                'date_of_birth' => '1996-04-12',
                'hire_date' => '2025-06-01',
            ],
            [
                'employee_code' => 'EMP007',
                'full_name' => 'Võ Thị Hạnh',
                'email' => 'hanh@example.com',
                'phone' => '0901000007',
                'gender' => 'female',
                'date_of_birth' => '1999-09-03',
                'hire_date' => '2025-07-15',
            ],
            [
                'employee_code' => 'EMP008',
                'full_name' => 'Trương Quốc Bảo',
                'email' => 'bao@example.com',
                'phone' => '0901000008',
                'gender' => 'male',
                'date_of_birth' => '1994-12-20',
                'hire_date' => '2024-11-01',
            ],
        ];

        $subordinates = [];

        foreach ($team as $row) {
            $employee = Employee::query()->updateOrCreate(
                ['employee_code' => $row['employee_code']],
                [
                    'department_id' => $itDepartment->id,
                    'position_id' => $positionId,
                    'manager_id' => $manager->id,
                    'full_name' => $row['full_name'],
                    'gender' => $row['gender'],
                    'date_of_birth' => $row['date_of_birth'],
                    'phone' => $row['phone'],
                    'email' => $row['email'],
                    'address' => 'TP. Hồ Chí Minh',
                    'hire_date' => $row['hire_date'],
                    'status' => 'active',
                ]
            );

            $subordinates[] = $employee;
        }

        $nvitUser = User::query()->where('username', 'nvit01')->first();
        if ($nvitUser) {
            Employee::query()->where('employee_code', 'EMP006')->update(['user_id' => $nvitUser->id]);
        }

        return $subordinates;
    }

    /**
     * @param  list<Employee>  $subordinates
     */
    private function seedLeaveRequests(Employee $manager, array $subordinates): void
    {
        $managerUserId = $manager->user_id ?? User::query()->where('username', 'manager')->value('id');

        if ($subordinates === []) {
            return;
        }

        $samples = [
            [
                'employee_code' => 'EMP006',
                'leave_type' => 'annual',
                'start_date' => now()->addDays(5)->toDateString(),
                'end_date' => now()->addDays(7)->toDateString(),
                'total_days' => 3,
                'reason' => 'Về quê nghỉ lễ — cần manager duyệt',
                'status' => LeaveRequest::STATUS_PENDING,
            ],
            [
                'employee_code' => 'EMP007',
                'leave_type' => 'sick',
                'start_date' => now()->subDays(1)->toDateString(),
                'end_date' => now()->addDays(1)->toDateString(),
                'total_days' => 3,
                'reason' => 'Cảm cúm, có giấy bác sĩ',
                'status' => LeaveRequest::STATUS_PENDING,
            ],
            [
                'employee_code' => 'EMP008',
                'leave_type' => 'unpaid',
                'start_date' => now()->addDays(10)->toDateString(),
                'end_date' => now()->addDays(12)->toDateString(),
                'total_days' => 3,
                'reason' => 'Giải quyết thủ tục cá nhân',
                'status' => LeaveRequest::STATUS_PENDING,
            ],
            [
                'employee_code' => 'EMP006',
                'leave_type' => 'annual',
                'start_date' => now()->subDays(20)->toDateString(),
                'end_date' => now()->subDays(18)->toDateString(),
                'total_days' => 3,
                'reason' => 'Du lịch ngắn ngày',
                'status' => LeaveRequest::STATUS_APPROVED,
                'approved_by' => $managerUserId,
                'approved_at' => now()->subDays(21),
            ],
            [
                'employee_code' => 'EMP007',
                'leave_type' => 'annual',
                'start_date' => now()->subDays(10)->toDateString(),
                'end_date' => now()->subDays(8)->toDateString(),
                'total_days' => 3,
                'reason' => 'Trùng lịch release — xin nghỉ',
                'status' => LeaveRequest::STATUS_REJECTED,
                'rejected_by' => $managerUserId,
                'rejected_at' => now()->subDays(11),
                'reject_reason' => 'Tuần release quan trọng, cần có mặt onsite',
            ],
        ];

        foreach ($samples as $sample) {
            $employeeId = Employee::query()->where('employee_code', $sample['employee_code'])->value('id');
            if (! $employeeId) {
                continue;
            }

            $exists = LeaveRequest::query()
                ->where('employee_id', $employeeId)
                ->where('start_date', $sample['start_date'])
                ->where('reason', $sample['reason'])
                ->exists();

            if ($exists) {
                continue;
            }

            LeaveRequest::query()->create([
                'employee_id' => $employeeId,
                'leave_type' => $sample['leave_type'],
                'start_date' => $sample['start_date'],
                'end_date' => $sample['end_date'],
                'total_days' => $sample['total_days'],
                'reason' => $sample['reason'],
                'status' => $sample['status'],
                'approved_by' => $sample['approved_by'] ?? null,
                'approved_at' => $sample['approved_at'] ?? null,
                'rejected_by' => $sample['rejected_by'] ?? null,
                'rejected_at' => $sample['rejected_at'] ?? null,
                'reject_reason' => $sample['reject_reason'] ?? null,
            ]);
        }
    }

    private function seedOvertimeRequests(Employee $manager): void
    {
        $managerUserId = $manager->user_id ?? User::query()->where('username', 'manager')->value('id');

        $samples = [
            [
                'employee_code' => 'EMP006',
                'work_date' => now()->addDays(2)->toDateString(),
                'start_time' => '18:00:00',
                'end_time' => '21:00:00',
                'total_hours' => 3,
                'reason' => 'Hoàn thiện module HRM trước deadline',
                'status' => OvertimeRequest::STATUS_PENDING,
            ],
            [
                'employee_code' => 'EMP007',
                'work_date' => now()->addDays(1)->toDateString(),
                'start_time' => '18:30:00',
                'end_time' => '22:00:00',
                'total_hours' => 3.5,
                'reason' => 'Fix bug production sau giờ làm',
                'status' => OvertimeRequest::STATUS_PENDING,
            ],
            [
                'employee_code' => 'EMP008',
                'work_date' => now()->subDays(3)->toDateString(),
                'start_time' => '08:00:00',
                'end_time' => '12:00:00',
                'total_hours' => 4,
                'reason' => 'Triển khai server cuối tuần',
                'status' => OvertimeRequest::STATUS_APPROVED,
                'approved_by' => $managerUserId,
                'approved_at' => now()->subDays(4),
            ],
            [
                'employee_code' => 'EMP007',
                'work_date' => now()->subDays(7)->toDateString(),
                'start_time' => '19:00:00',
                'end_time' => '21:00:00',
                'total_hours' => 2,
                'reason' => 'Báo cáo KPI không cần tăng ca',
                'status' => OvertimeRequest::STATUS_REJECTED,
                'approved_by' => $managerUserId,
                'approved_at' => now()->subDays(8),
                'reject_reason' => 'Công việc có thể hoàn thành trong giờ hành chính',
            ],
        ];

        foreach ($samples as $sample) {
            $employeeId = Employee::query()->where('employee_code', $sample['employee_code'])->value('id');
            if (! $employeeId) {
                continue;
            }

            $exists = OvertimeRequest::query()
                ->where('employee_id', $employeeId)
                ->whereDate('work_date', $sample['work_date'])
                ->where('reason', $sample['reason'])
                ->exists();

            if ($exists) {
                continue;
            }

            OvertimeRequest::query()->create([
                'employee_id' => $employeeId,
                'work_date' => $sample['work_date'],
                'start_time' => $sample['start_time'],
                'end_time' => $sample['end_time'],
                'total_hours' => $sample['total_hours'],
                'reason' => $sample['reason'],
                'status' => $sample['status'],
                'approved_by' => $sample['approved_by'] ?? null,
                'approved_at' => $sample['approved_at'] ?? null,
                'reject_reason' => $sample['reject_reason'] ?? null,
            ]);
        }
    }

    private function ensureContractLinks(): void
    {
        $employees = Employee::query()->whereIn('employee_code', ['EMP006', 'EMP007', 'EMP008'])->get();

        foreach ($employees as $employee) {
            $exists = DB::table('contracts')->where('employee_id', $employee->id)->exists();
            if ($exists) {
                continue;
            }

            DB::table('contracts')->insert([
                'employee_id' => $employee->id,
                'department_id' => $employee->department_id,
                'position_id' => $employee->position_id,
                'contract_type_id' => 3,
                'contract_code' => 'HD-' . $employee->employee_code,
                'start_date' => $employee->hire_date,
                'end_date' => now()->addYear()->toDateString(),
                'salary' => 22000000,
                'allowance' => 2000000,
                'status' => 'active',
                'signed_date' => $employee->hire_date,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
