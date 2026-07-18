<?php

namespace Database\Seeders;

use App\Models\EarlyLeaveRequest;
use App\Models\Employee;
use App\Models\KpiTeamReport;
use App\Models\LeaderTeamReport;
use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;
use App\Models\Role;
use App\Models\SalaryAdvance;
use App\Models\Team;
use App\Models\TeamMembershipRequest;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

/**
 * Dữ liệu demo đầy đủ để test TẤT CẢ chức năng (Admin · Manager · Employee · Accountant).
 *
 * Chạy: php artisan migrate --force && php artisan db:seed --class=FullTestDataSeeder
 *
 * Mật khẩu tất cả tài khoản: password
 */
class FullTestDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(MasterDemoSeeder::class);

        $this->clearExtendedTables();
        $this->seedExtraRolesAndUsers();
        $this->restructureItTeam();
        $this->seedTeam();
        $this->seedTwoTierApprovals();
        $this->seedTwoTierKpi();
        $this->seedEarlyLeave();
        $this->seedTeamMembershipRequests();
        $this->seedLeaderTeamReports();
        $this->seedAccountantData();

        $this->printSummary();
    }

    private function clearExtendedTables(): void
    {
        $tables = [
            'team_membership_requests',
            'leader_team_reports',
            'kpi_team_reports',
            'early_leave_requests',
            'salary_advance_deductions',
            'salary_advances',
            'tax_dependents',
            'employee_tax_profiles',
            'employee_insurances',
        ];

        Schema::disableForeignKeyConstraints();
        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->truncate();
            }
        }
        Schema::enableForeignKeyConstraints();
    }

    private function seedExtraRolesAndUsers(): void
    {
        DB::table('roles')->updateOrInsert(
            ['name' => Role::ACCOUNTANT],
            ['description' => 'Kế toán', 'created_at' => now(), 'updated_at' => now()]
        );

        $employeeRoleId = Role::query()->where('name', Role::EMPLOYEE)->value('id');
        $accountantRoleId = Role::query()->where('name', Role::ACCOUNTANT)->value('id');

        User::query()->updateOrCreate(
            ['username' => 'leader'],
            [
                'name' => 'Trương Quốc Bảo',
                'email' => 'leader@example.com',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'role_id' => $employeeRoleId,
                'status' => 'active',
            ]
        );

        User::query()->updateOrCreate(
            ['username' => 'accountant'],
            [
                'name' => 'Trần Thị Bình',
                'email' => 'accountant@example.com',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'role_id' => $accountantRoleId,
                'status' => 'active',
            ]
        );

        $leaderUser = User::query()->where('username', 'leader')->firstOrFail();
        $accountantUser = User::query()->where('username', 'accountant')->firstOrFail();

        Employee::query()->where('employee_code', 'EMP003')->update([
            'user_id' => $leaderUser->id,
            'email' => $leaderUser->email,
        ]);

        Employee::query()->where('employee_code', 'EMP009')->update([
            'user_id' => $accountantUser->id,
            'email' => $accountantUser->email,
        ]);

        // emp_it02 → thực tập sinh EMP005
        $empIt02 = User::query()->where('username', 'emp_it02')->first();
        if ($empIt02) {
            Employee::query()->where('employee_code', 'EMP005')->update([
                'user_id' => $empIt02->id,
                'email' => $empIt02->email,
            ]);
        }
    }

    private function restructureItTeam(): void
    {
        $leaderId = Employee::query()->where('employee_code', 'EMP003')->value('id');

        Employee::query()
            ->whereIn('employee_code', ['EMP004', 'EMP005'])
            ->update(['manager_id' => $leaderId]);
    }

    private function seedTeam(): void
    {
        $itDeptId = DB::table('departments')->where('department_code', 'IT')->value('id');
        $leaderId = Employee::query()->where('employee_code', 'EMP003')->value('id');
        $adminId = User::query()->where('username', 'admin')->value('id');

        Team::query()->updateOrCreate(
            ['leader_employee_id' => $leaderId],
            [
                'department_id' => $itDeptId,
                'name' => 'Nhóm Dev IT',
                'description' => 'Nhóm phát triển phần mềm HRM',
                'created_by' => $adminId,
                'status' => Team::STATUS_ACTIVE,
            ]
        );
    }

    private function seedTwoTierApprovals(): void
    {
        $leaderUserId = User::query()->where('username', 'leader')->value('id');
        $managerUserId = User::query()->where('username', 'manager')->value('id');
        $emp004Id = Employee::query()->where('employee_code', 'EMP004')->value('id');
        $emp005Id = Employee::query()->where('employee_code', 'EMP005')->value('id');

        // Cập nhật đơn pending hiện có của EMP004 → chờ Leader duyệt
        LeaveRequest::query()
            ->where('employee_id', $emp004Id)
            ->where('status', LeaveRequest::STATUS_PENDING)
            ->update([
                'leader_approved_by' => null,
                'leader_approved_at' => null,
                'approved_by' => null,
                'approved_at' => null,
            ]);

        // Đơn đã qua Leader, chờ Manager
        LeaveRequest::query()->create([
            'employee_id' => $emp005Id,
            'leave_type' => 'annual',
            'start_date' => now()->addDays(12)->toDateString(),
            'end_date' => now()->addDays(14)->toDateString(),
            'total_days' => 3,
            'reason' => 'Du lịch — Leader đã duyệt, chờ Manager',
            'status' => LeaveRequest::STATUS_PENDING,
            'leader_approved_by' => $leaderUserId,
            'leader_approved_at' => now()->subDay(),
        ]);

        // Tăng ca chờ Leader
        OvertimeRequest::query()
            ->where('employee_id', $emp004Id)
            ->where('status', OvertimeRequest::STATUS_PENDING)
            ->update([
                'leader_approved_by' => null,
                'leader_approved_at' => null,
                'approved_by' => null,
                'approved_at' => null,
            ]);

        OvertimeRequest::query()->create([
            'employee_id' => $emp005Id,
            'work_date' => now()->addDays(3)->toDateString(),
            'start_time' => '18:00:00',
            'end_time' => '20:00:00',
            'total_hours' => 2,
            'reason' => 'Hỗ trợ deploy — Leader đã duyệt',
            'status' => OvertimeRequest::STATUS_PENDING,
            'leader_approved_by' => $leaderUserId,
            'leader_approved_at' => now(),
        ]);

        // Đơn đã hoàn tất 2 bậc
        LeaveRequest::query()->create([
            'employee_id' => $emp004Id,
            'leave_type' => 'sick',
            'start_date' => now()->subDays(5)->toDateString(),
            'end_date' => now()->subDays(4)->toDateString(),
            'total_days' => 2,
            'reason' => 'Cảm cúm — đã duyệt đủ 2 bậc',
            'status' => LeaveRequest::STATUS_APPROVED,
            'leader_approved_by' => $leaderUserId,
            'leader_approved_at' => now()->subDays(6),
            'approved_by' => $managerUserId,
            'approved_at' => now()->subDays(5),
        ]);
    }

    private function seedTwoTierKpi(): void
    {
        $leaderId = Employee::query()->where('employee_code', 'EMP003')->value('id');
        $emp004Id = Employee::query()->where('employee_code', 'EMP004')->value('id');
        $emp005Id = Employee::query()->where('employee_code', 'EMP005')->value('id');
        $kpi3Id = DB::table('kpis')->where('code', 'KPI0003')->value('id');

        DB::table('kpi_assignments')
            ->whereIn('kpi_id', function ($q) {
                $q->select('id')->from('kpis')->whereIn('code', ['KPI0002', 'KPI0003']);
            })
            ->update([
                'leader_employee_id' => $leaderId,
                'leader_assigned_at' => now()->subDays(3),
                'status' => 'active',
            ]);

        $assignmentId = DB::table('kpi_assignments')
            ->where('kpi_id', $kpi3Id)
            ->value('id');

        if (! $assignmentId) {
            return;
        }

        foreach ([
            [$emp004Id, 70, 'in_progress', 75, 'Tiến độ ổn, cần hoàn thiện test'],
            [$emp005Id, 40, 'pending', null, null],
        ] as [$empId, $progress, $status, $leaderScore, $leaderReview]) {
            $exists = DB::table('employee_kpis')
                ->where('assignment_id', $assignmentId)
                ->where('employee_id', $empId)
                ->exists();

            if ($exists) {
                DB::table('employee_kpis')
                    ->where('assignment_id', $assignmentId)
                    ->where('employee_id', $empId)
                    ->update([
                        'progress' => $progress,
                        'status' => $status,
                        'leader_score' => $leaderScore,
                        'leader_review' => $leaderReview,
                    ]);
            } else {
                DB::table('employee_kpis')->insert([
                    'assignment_id' => $assignmentId,
                    'employee_id' => $empId,
                    'kpi_id' => $kpi3Id,
                    'target' => '15 task/sprint',
                    'progress' => $progress,
                    'status' => $status,
                    'leader_score' => $leaderScore,
                    'leader_review' => $leaderReview,
                    'deadline' => '2026-09-30',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        KpiTeamReport::query()->create([
            'assignment_id' => $assignmentId,
            'leader_employee_id' => $leaderId,
            'summary' => 'Nhóm hoàn thành 70% task sprint Q3, cần tăng tốc tuần cuối.',
            'total_members' => 2,
            'completed_count' => 0,
            'avg_progress' => 55,
            'avg_leader_score' => 75,
            'status' => KpiTeamReport::STATUS_SUBMITTED,
            'submitted_at' => now()->subHours(6),
        ]);
    }

    private function seedEarlyLeave(): void
    {
        $managerUserId = User::query()->where('username', 'manager')->value('id');
        $emp004Id = Employee::query()->where('employee_code', 'EMP004')->value('id');
        $emp005Id = Employee::query()->where('employee_code', 'EMP005')->value('id');

        EarlyLeaveRequest::query()->create([
            'employee_id' => $emp004Id,
            'request_date' => now()->addDays(2)->toDateString(),
            'leave_time' => '16:00:00',
            'reason' => 'Khám bệnh định kỳ — chờ Manager duyệt',
            'status' => EarlyLeaveRequest::STATUS_PENDING,
        ]);

        EarlyLeaveRequest::query()->create([
            'employee_id' => $emp005Id,
            'request_date' => now()->subDays(2)->toDateString(),
            'leave_time' => '15:30:00',
            'reason' => 'Việc gia đình — đã duyệt',
            'status' => EarlyLeaveRequest::STATUS_APPROVED,
            'approved_by' => $managerUserId,
            'approved_at' => now()->subDays(3),
        ]);
    }

    private function seedTeamMembershipRequests(): void
    {
        $leaderId = Employee::query()->where('employee_code', 'EMP003')->value('id');
        $leaderUserId = User::query()->where('username', 'leader')->value('id');
        $emp010Id = Employee::query()->where('employee_code', 'EMP010')->value('id');

        TeamMembershipRequest::query()->create([
            'leader_id' => $leaderId,
            'employee_id' => $emp010Id,
            'action' => TeamMembershipRequest::ACTION_ADD,
            'reason' => 'Hoàng Văn Em hỗ trợ content cho dự án HRM',
            'status' => TeamMembershipRequest::STATUS_PENDING,
            'requested_by' => $leaderUserId,
        ]);
    }

    private function seedLeaderTeamReports(): void
    {
        $leaderId = Employee::query()->where('employee_code', 'EMP003')->value('id');
        $managerUserId = User::query()->where('username', 'manager')->value('id');

        LeaderTeamReport::query()->create([
            'leader_employee_id' => $leaderId,
            'manager_user_id' => $managerUserId,
            'period_month' => 7,
            'period_year' => 2026,
            'title' => 'Báo cáo tiến độ nhóm Dev IT — Tháng 7/2026',
            'work_progress' => 'Hoàn thành 80% module KPI 2 bậc và chat nội bộ.',
            'team_results' => 'Không có sự cố production. 2 nhân viên đạt chấm công ≥95%.',
            'member_count' => 2,
            'kpi_total' => 2,
            'kpi_completed' => 1,
            'avg_kpi_progress' => 55,
            'total_work_days' => 44,
            'total_late_days' => 2,
            'status' => LeaderTeamReport::STATUS_SUBMITTED,
            'submitted_at' => now()->subHours(12),
        ]);

        LeaderTeamReport::query()->create([
            'leader_employee_id' => $leaderId,
            'manager_user_id' => $managerUserId,
            'period_month' => 6,
            'period_year' => 2026,
            'title' => 'Báo cáo tháng 6/2026',
            'work_progress' => 'Triển khai module chấm công khuôn mặt.',
            'team_results' => 'Release v1.2 đúng hạn.',
            'member_count' => 2,
            'status' => LeaderTeamReport::STATUS_DRAFT,
        ]);
    }

    private function seedAccountantData(): void
    {
        $emp004Id = Employee::query()->where('employee_code', 'EMP004')->value('id');
        $emp009Id = Employee::query()->where('employee_code', 'EMP009')->value('id');
        $accountantUserId = User::query()->where('username', 'accountant')->value('id');
        $empUserId = User::query()->where('username', 'employee')->value('id');

        DB::table('employee_insurances')->insert([
            [
                'employee_id' => $emp004Id,
                'social_insurance_number' => 'BHXH-EMP004',
                'health_insurance_code' => 'BHYT-EMP004',
                'contribution_salary' => 12000000,
                'bhxh_employee_rate' => 0.08,
                'bhxh_employer_rate' => 0.175,
                'bhyt_employee_rate' => 0.015,
                'bhyt_employer_rate' => 0.03,
                'bhtn_employee_rate' => 0.01,
                'bhtn_employer_rate' => 0.01,
                'start_date' => '2025-04-01',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'employee_id' => $emp009Id,
                'social_insurance_number' => 'BHXH-EMP009',
                'health_insurance_code' => 'BHYT-EMP009',
                'contribution_salary' => 18000000,
                'bhxh_employee_rate' => 0.08,
                'bhxh_employer_rate' => 0.175,
                'bhyt_employee_rate' => 0.015,
                'bhyt_employer_rate' => 0.03,
                'bhtn_employee_rate' => 0.01,
                'bhtn_employer_rate' => 0.01,
                'start_date' => '2023-01-01',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('employee_tax_profiles')->insert([
            [
                'employee_id' => $emp004Id,
                'tax_code' => '8123456789',
                'personal_deduction' => 11000000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('tax_dependents')->insert([
            'employee_id' => $emp004Id,
            'status' => 'pending',
            'full_name' => 'Phạm Văn Con',
            'relationship' => 'child',
            'date_of_birth' => '2020-05-10',
            'monthly_deduction' => 4400000,
            'start_date' => '2026-07-01',
            'is_active' => true,
            'requested_by' => $empUserId,
            'approved_by' => null,
            'approved_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('tax_dependents')->insert([
            'employee_id' => $emp004Id,
            'status' => 'approved',
            'full_name' => 'Nguyễn Thị Mẹ',
            'relationship' => 'parent',
            'date_of_birth' => '1965-03-15',
            'monthly_deduction' => 4400000,
            'start_date' => '2025-01-01',
            'is_active' => true,
            'requested_by' => $empUserId,
            'approved_by' => $accountantUserId,
            'approved_at' => now()->subDays(10),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        SalaryAdvance::query()->create([
            'advance_code' => 'UL-202607-001',
            'employee_id' => $emp004Id,
            'amount' => 5000000,
            'amount_settled' => 0,
            'request_date' => now()->subDays(3)->toDateString(),
            'reason' => 'Chi phí y tế đột xuất',
            'status' => SalaryAdvance::STATUS_PENDING,
            'requested_by' => $empUserId,
        ]);

        SalaryAdvance::query()->create([
            'advance_code' => 'UL-202606-002',
            'employee_id' => $emp004Id,
            'amount' => 8000000,
            'amount_settled' => 3000000,
            'request_date' => '2026-06-01',
            'reason' => 'Sửa nhà — đang trừ dần',
            'status' => SalaryAdvance::STATUS_PARTIAL,
            'requested_by' => $empUserId,
            'approved_by' => $accountantUserId,
            'approved_at' => '2026-06-02',
        ]);
    }

    private function printSummary(): void
    {
        $this->command?->newLine();
        $this->command?->info('✅  FullTestDataSeeder hoàn tất — dữ liệu demo sẵn sàng test!');
        $this->command?->newLine();

        $this->command?->table(
            ['Vai trò', 'Username', 'Password', 'URL / Ghi chú'],
            [
                ['Admin', 'admin', 'password', '/admin/dashboard'],
                ['Manager IT', 'manager', 'password', '/manager/dashboard · duyệt nghỉ/OT'],
                ['Manager KD', 'manager_sale', 'password', '/manager/dashboard · phòng Kinh doanh'],
                ['Trưởng nhóm IT', 'leader', 'password', '/employee/dashboard · nhóm Dev IT (EMP003)'],
                ['Employee', 'employee', 'password', '/employee/dashboard · Phạm Thị Dung EMP004'],
                ['Employee', 'emp_it02', 'password', '/employee/dashboard · Nguyễn Minh Khoa EMP005'],
                ['Employee', 'emp_sale01', 'password', '/employee/dashboard · phòng KD'],
                ['Accountant', 'accountant', 'password', '/accountant/dashboard · Trần Thị Bình EMP009'],
            ]
        );

        $this->command?->newLine();
        $this->command?->line('  Chức năng có sẵn dữ liệu test:');
        $this->command?->line('  • Manager: duyệt nghỉ/tăng ca, KPI, nhóm, đề xuất thành viên, về sớm');
        $this->command?->line('  • Employee: chấm công, nghỉ phép, tăng ca, về sớm, ứng lương, NPT');
        $this->command?->line('  • Accountant: lương, BHXH, thuế, ứng lương, hợp đồng');
        $this->command?->newLine();
    }
}
