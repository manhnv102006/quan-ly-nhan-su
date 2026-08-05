<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Support\LeaveCapacityRules;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Dữ liệu demo quy tắc nghỉ phép tối đa 30% nhân sự/phòng ban/ngày.
 *
 * Kịch bản chính — Phòng IT (10 NV → tối đa 3 người/ngày):
 * - 3 đơn đã duyệt cùng ngày 18–20/08/2026 (đạt hạn mức 30%)
 * - 1 đơn chờ duyệt cùng khoảng → quản lý duyệt sẽ bị chặn
 * - 1 đơn chờ duyệt khác để test bulk duyệt
 *
 * Kịch bản phụ — Phòng Kinh doanh (3 NV → tối đa 1 người/ngày):
 * - 1 đơn đã duyệt + 1 đơn chờ duyệt trùng ngày
 *
 * Chạy: php artisan db:seed --class=LeaveCapacityDemoSeeder
 */
class LeaveCapacityDemoSeeder extends Seeder
{
    private const DEMO_TAG = '[DEMO-30%]';

    private int $managerUserId;

    private int $itManagerEmployeeId;

    private int $itDepartmentId;

    private int $saleDepartmentId;

    private int $staffPositionId;

    /** @var list<string> */
    private array $demoEmployeeCodes = [
        'LEAVE30-01',
        'LEAVE30-02',
        'LEAVE30-03',
        'LEAVE30-04',
        'LEAVE30-05',
    ];

    public function run(): void
    {
        $this->managerUserId = (int) (DB::table('users')->where('username', 'manager')->value('id') ?? 1);
        $this->itDepartmentId = (int) Department::query()->where('department_code', 'IT')->value('id');
        $this->saleDepartmentId = (int) Department::query()->where('department_code', 'SALE')->value('id');
        $this->itManagerEmployeeId = (int) (Employee::query()->where('employee_code', 'EMP002')->value('id') ?? 0);
        $this->staffPositionId = (int) (DB::table('positions')->where('position_name', 'Nhân viên')->value('id') ?? 4);

        if (! $this->itDepartmentId) {
            $this->command?->warn('Không tìm thấy phòng IT.');

            return;
        }

        $this->command?->info('Đang tạo dữ liệu demo giới hạn nghỉ phép 30%...');

        $this->ensureItHeadcountForDemo();
        $this->clearDemoLeaves();

        $capacityStart = '2026-08-18';
        $capacityEnd = '2026-08-20';

        $itEmployees = Employee::query()
            ->where('department_id', $this->itDepartmentId)
            ->where('status', 'active')
            ->orderBy('id')
            ->get();

        $headcount = $itEmployees->count();
        $maxSlots = LeaveCapacityRules::slotsFor($headcount, LeaveCapacityRules::RATIO_EMPLOYEE);

        $regularItEmployees = $itEmployees
            ->reject(fn (Employee $e) => $e->id === $this->itManagerEmployeeId)
            ->values();

        if ($regularItEmployees->count() < 4) {
            $this->command?->warn('Phòng IT không đủ nhân viên để demo.');

            return;
        }

        // ── IT: 3 đơn đã duyệt (đạt hạn mức 30%) ──
        foreach ($regularItEmployees->take(min(3, $maxSlots)) as $employee) {
            $this->insertLeave(
                employeeId: $employee->id,
                start: $capacityStart,
                end: $capacityEnd,
                days: 3,
                reason: self::DEMO_TAG.' Phòng IT — đã duyệt (đạt hạn mức '.$maxSlots.'/'.$headcount.' NV)',
                status: LeaveRequest::STATUS_APPROVED,
            );
        }

        // ── IT: đơn chờ duyệt — quản lý duyệt sẽ bị chặn ──
        $blockedEmployee = $regularItEmployees->get(3);
        if ($blockedEmployee) {
            $this->insertLeave(
                employeeId: $blockedEmployee->id,
                start: $capacityStart,
                end: $capacityEnd,
                days: 3,
                reason: self::DEMO_TAG.' Phòng IT — chờ duyệt (vượt hạn mức 30%, thử duyệt sẽ bị chặn)',
                status: LeaveRequest::STATUS_PENDING,
            );
        }

        $extraPending = $regularItEmployees->get(4);
        if ($extraPending) {
            $this->insertLeave(
                employeeId: $extraPending->id,
                start: $capacityStart,
                end: $capacityEnd,
                days: 3,
                reason: self::DEMO_TAG.' Phòng IT — chờ duyệt #2 (test duyệt hàng loạt)',
                status: LeaveRequest::STATUS_PENDING,
            );
        }

        // ── IT: đơn đã duyệt ngày khác (chưa đạt hạn mức) ──
        $soloEmployee = $regularItEmployees->get(5) ?? $regularItEmployees->last();
        if ($soloEmployee) {
            $this->insertLeave(
                employeeId: $soloEmployee->id,
                start: '2026-08-25',
                end: '2026-08-26',
                days: 2,
                reason: self::DEMO_TAG.' Phòng IT — đã duyệt ngày 25–26/08 (còn chỗ trống)',
                status: LeaveRequest::STATUS_APPROVED,
            );
        }

        // ── IT: đơn bị từ chối do hết hạn mức ──
        $rejectedEmployee = $regularItEmployees->get(6) ?? $regularItEmployees->last();
        if ($rejectedEmployee && $rejectedEmployee->id !== ($soloEmployee?->id)) {
            $this->insertLeave(
                employeeId: $rejectedEmployee->id,
                start: $capacityStart,
                end: $capacityEnd,
                days: 3,
                reason: self::DEMO_TAG.' Phòng IT — đã từ chối (phòng ban đủ 30%)',
                status: LeaveRequest::STATUS_REJECTED,
                rejectReason: 'Phòng ban đã đạt giới hạn 30% nhân viên nghỉ cùng ngày (3/3 người).',
            );
        }

        // ── Kinh doanh: 3 NV → tối đa 1 người/ngày ──
        $this->seedSaleDepartmentDemo($capacityStart);

        $this->printSummary($headcount, $maxSlots, $capacityStart, $capacityEnd);
    }

    private function ensureItHeadcountForDemo(): void
    {
        $targetHeadcount = 10;

        foreach ($this->demoEmployeeCodes as $index => $code) {
            Employee::query()->updateOrCreate(
                ['employee_code' => $code],
                [
                    'user_id' => null,
                    'department_id' => $this->itDepartmentId,
                    'position_id' => $this->staffPositionId,
                    'manager_id' => $this->itManagerEmployeeId ?: null,
                    'full_name' => 'NV Demo Nghỉ 30% '.str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT),
                    'gender' => $index % 2 === 0 ? 'male' : 'female',
                    'date_of_birth' => '1996-01-'.str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT),
                    'phone' => '0903000'.str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT),
                    'email' => strtolower($code).'@demo.local',
                    'hire_date' => '2025-06-01',
                    'status' => 'active',
                ]
            );
        }

        $current = Employee::query()
            ->where('department_id', $this->itDepartmentId)
            ->where('status', 'active')
            ->count();

        if ($current < $targetHeadcount) {
            $this->command?->warn("Phòng IT hiện có {$current} NV (mục tiêu {$targetHeadcount}). Đã thêm ".count($this->demoEmployeeCodes).' NV demo.');
        }
    }

    private function clearDemoLeaves(): void
    {
        LeaveRequest::query()
            ->where('reason', 'like', '%'.self::DEMO_TAG.'%')
            ->delete();
    }

    private function seedSaleDepartmentDemo(string $date): void
    {
        if (! $this->saleDepartmentId) {
            return;
        }

        $saleEmployees = Employee::query()
            ->where('department_id', $this->saleDepartmentId)
            ->where('status', 'active')
            ->orderBy('id')
            ->get()
            ->reject(fn (Employee $e) => $e->employee_code === 'EMP006')
            ->values();

        if ($saleEmployees->count() < 2) {
            return;
        }

        $this->insertLeave(
            employeeId: $saleEmployees[0]->id,
            start: $date,
            end: $date,
            days: 1,
            reason: self::DEMO_TAG.' Phòng KD — đã duyệt (1/1 hạn mức 30%)',
            status: LeaveRequest::STATUS_APPROVED,
        );

        $this->insertLeave(
            employeeId: $saleEmployees[1]->id,
            start: $date,
            end: $date,
            days: 1,
            reason: self::DEMO_TAG.' Phòng KD — chờ duyệt (phòng ban đã đủ 30%)',
            status: LeaveRequest::STATUS_PENDING,
        );
    }

    private function insertLeave(
        int $employeeId,
        string $start,
        string $end,
        int $days,
        string $reason,
        string $status,
        ?string $rejectReason = null,
    ): void {
        $approved = $status === LeaveRequest::STATUS_APPROVED;
        $rejected = $status === LeaveRequest::STATUS_REJECTED;

        DB::table('leave_requests')->insert([
            'employee_id' => $employeeId,
            'leave_type' => 'annual',
            'start_date' => $start,
            'end_date' => $end,
            'total_days' => $days,
            'reason' => $reason,
            'status' => $status,
            'approved_by' => $approved ? $this->managerUserId : null,
            'approved_at' => $approved ? Carbon::parse($start)->subDay() : null,
            'reject_reason' => $rejectReason,
            'rejected_by' => $rejected ? $this->managerUserId : null,
            'rejected_at' => $rejected ? now() : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function printSummary(int $headcount, int $maxSlots, string $start, string $end): void
    {
        $period = Carbon::parse($start)->format('d/m/Y').' → '.Carbon::parse($end)->format('d/m/Y');

        $this->command?->info('Hoàn tất demo giới hạn nghỉ phép 30%!');
        $this->command?->table(
            ['Thông tin', 'Giá trị'],
            [
                ['Phòng IT — nhân sự active', $headcount.' người'],
                ['Hạn mức 30%/ngày', $maxSlots.' người'],
                ['Khoảng demo chính', $period],
                ['Đơn đã duyệt (IT)', min(3, $maxSlots).' đơn — đạt hạn mức'],
                ['Đơn chờ duyệt (IT)', '2 đơn — duyệt sẽ bị chặn'],
                ['Phòng Kinh doanh', '1 đã duyệt + 1 chờ duyệt trùng ngày'],
            ]
        );

        $this->command?->info('Cách test:');
        $this->command?->line('  1. Đăng nhập manager → Duyệt nghỉ phép → thử duyệt đơn [DEMO-30%] chờ duyệt ngày 18–20/08');
        $this->command?->line('  2. Đăng nhập employee (NV IT chưa nghỉ) → Tạo đơn 18–20/08 → hệ thống chặn gửi đơn');
        $this->command?->line('  3. Thử duyệt đơn ngày 25–26/08 → duyệt được (còn chỗ trống)');
    }
}
