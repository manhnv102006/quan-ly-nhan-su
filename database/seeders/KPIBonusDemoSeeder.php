<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Payroll;
use App\Models\PayrollPeriod;
use App\Services\PayrollService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Dữ liệu mẫu KPI + thưởng lương (thang điểm → tiền thưởng):
 * - < 70%: 0đ | 70–79%: 300.000đ | 80–89%: 700.000đ | 90–99%: 1.200.000đ | ≥100%: 2.000.000đ
 *
 * Chạy: php artisan db:seed --class=KPIBonusDemoSeeder
 */
class KPIBonusDemoSeeder extends Seeder
{
    private int $adminUserId;

    private int $itManagerUserId;

    private int $saleManagerUserId;

    private int $itDepartmentId;

    private int $saleDepartmentId;

    /** @var array<string, int> */
    private array $kpiIds = [];

    /** @var array<string, int> */
    private array $assignmentIds = [];

    public function run(): void
    {
        $this->adminUserId = (int) (DB::table('users')->where('username', 'admin')->value('id') ?? 1);
        $this->itManagerUserId = (int) (DB::table('users')->where('username', 'manager')->value('id') ?? $this->adminUserId);
        $this->saleManagerUserId = (int) (DB::table('users')->where('username', 'manager_sale')->value('id') ?? $this->adminUserId);
        $this->itDepartmentId = (int) DB::table('departments')->where('department_code', 'IT')->value('id');
        $this->saleDepartmentId = (int) DB::table('departments')->where('department_code', 'SALE')->value('id');

        $this->command?->info('Đang tạo dữ liệu mẫu KPI & thưởng...');

        $this->clearDemoData();
        $this->seedMonthlyKpis();
        $this->seedAssignments();
        $this->seedEmployeeKpis();
        $this->recalculatePayrollBonuses([8, 9, 10, 11], 2026);

        $this->printSummary();
    }

    private function clearDemoData(): void
    {
        $demoKpiIds = DB::table('kpis')->where('code', 'like', 'KPI-DEMO-%')->pluck('id');

        if ($demoKpiIds->isNotEmpty()) {
            DB::table('employee_kpis')->whereIn('kpi_id', $demoKpiIds)->delete();
            DB::table('kpi_assignments')->whereIn('kpi_id', $demoKpiIds)->delete();
            DB::table('kpi_tasks')->whereIn('kpi_id', $demoKpiIds)->delete();
            DB::table('kpi_department')->whereIn('kpi_id', $demoKpiIds)->delete();
            DB::table('kpis')->whereIn('id', $demoKpiIds)->delete();
        }
    }

    private function seedMonthlyKpis(): void
    {
        $months = [
            8 => ['label' => '08/2026', 'start' => '2026-08-01', 'end' => '2026-08-31'],
            9 => ['label' => '09/2026', 'start' => '2026-09-01', 'end' => '2026-09-30'],
            10 => ['label' => '10/2026', 'start' => '2026-10-01', 'end' => '2026-10-31'],
            11 => ['label' => '11/2026', 'start' => '2026-11-01', 'end' => '2026-11-30'],
        ];

        foreach ($months as $month => $range) {
            $this->createKpi(
                code: sprintf('KPI-DEMO-IT-%02d', $month),
                title: 'KPI phòng IT tháng '.$range['label'],
                description: 'Đánh giá chấm công, chất lượng công việc và hoàn thành task trong tháng '.$range['label'].'.',
                departmentId: $this->itDepartmentId,
                start: $range['start'],
                end: $range['end'],
                tasks: [
                    ['Check-in đúng giờ ≥95% ngày làm việc', 'Theo dữ liệu chấm công HRM'],
                    ['Hoàn thành task được giao đúng deadline', 'Cập nhật trên board dự án'],
                    ['Tham gia review code / hỗ trợ đồng nghiệp', null],
                ],
            );

            $this->createKpi(
                code: sprintf('KPI-DEMO-SA-%02d', $month),
                title: 'KPI phòng Kinh doanh tháng '.$range['label'],
                description: 'Đánh giá doanh số, chăm sóc khách hàng và báo cáo tháng '.$range['label'].'.',
                departmentId: $this->saleDepartmentId,
                start: $range['start'],
                end: $range['end'],
                tasks: [
                    ['Đạt mục tiêu doanh số cá nhân', 'Báo cáo cuối tháng'],
                    ['Chăm sóc ≥20 khách hàng cũ', 'Ghi nhận trên CRM'],
                    ['Gửi báo cáo tuần cho trưởng phòng', null],
                ],
            );
        }
    }

    /**
     * @param  list<array{0: string, 1: ?string}>  $tasks
     */
    private function createKpi(
        string $code,
        string $title,
        string $description,
        int $departmentId,
        string $start,
        string $end,
        array $tasks,
    ): void {
        $kpiId = DB::table('kpis')->insertGetId([
            'code' => $code,
            'title' => $title,
            'description' => $description,
            'target' => '100',
            'unit' => '%',
            'weight' => 100,
            'max_score' => 100,
            'period' => 'month',
            'start_date' => $start,
            'end_date' => $end,
            'positions' => json_encode(['manager']),
            'department_id' => $departmentId,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->kpiIds[$code] = $kpiId;

        DB::table('kpi_department')->insert([
            'kpi_id' => $kpiId,
            'department_id' => $departmentId,
        ]);

        foreach ($tasks as $order => [$taskTitle, $taskDesc]) {
            DB::table('kpi_tasks')->insert([
                'kpi_id' => $kpiId,
                'title' => $taskTitle,
                'description' => $taskDesc,
                'sort_order' => $order,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function seedAssignments(): void
    {
        foreach ([8, 9, 10, 11] as $month) {
            $start = Carbon::create(2026, $month, 1)->toDateString();
            $end = Carbon::create(2026, $month, 1)->endOfMonth()->toDateString();

            $itCode = sprintf('KPI-DEMO-IT-%02d', $month);
            $saCode = sprintf('KPI-DEMO-SA-%02d', $month);

            $this->assignmentIds[$itCode] = DB::table('kpi_assignments')->insertGetId([
                'kpi_id' => $this->kpiIds[$itCode],
                'manager_id' => $this->itManagerUserId,
                'target' => 100,
                'start_date' => $start,
                'end_date' => $end,
                'note' => 'Giao KPI demo tháng '.str_pad((string) $month, 2, '0', STR_PAD_LEFT).'/2026',
                'status' => $month <= 10 ? 'completed' : 'active',
                'assigned_by' => $this->adminUserId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->assignmentIds[$saCode] = DB::table('kpi_assignments')->insertGetId([
                'kpi_id' => $this->kpiIds[$saCode],
                'manager_id' => $this->saleManagerUserId,
                'target' => 100,
                'start_date' => $start,
                'end_date' => $end,
                'note' => 'Giao KPI demo tháng '.str_pad((string) $month, 2, '0', STR_PAD_LEFT).'/2026',
                'status' => $month <= 10 ? 'completed' : 'active',
                'assigned_by' => $this->adminUserId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function seedEmployeeKpis(): void
    {
        $emp = fn (string $code) => (int) (Employee::query()->where('employee_code', $code)->value('id') ?? 0);

        /** @var array<int, array<string, array{score: ?float, progress: int, status: string, comment: ?string}>> $matrix */
        $matrix = [
            8 => [
                'EMP003' => ['score' => 96.0, 'progress' => 100, 'status' => 'completed', 'comment' => 'Xuất sắc — thưởng 1.200.000đ'],
                'EMP004' => ['score' => 84.0, 'progress' => 100, 'status' => 'completed', 'comment' => 'Khá — thưởng 700.000đ'],
                'EMP005' => ['score' => 74.0, 'progress' => 100, 'status' => 'completed', 'comment' => 'Đạt mức tối thiểu — thưởng 300.000đ'],
                'NV26080008' => ['score' => 65.0, 'progress' => 100, 'status' => 'completed', 'comment' => 'Chưa đạt 70% — không thưởng'],
                'EMP007' => ['score' => 100.0, 'progress' => 100, 'status' => 'completed', 'comment' => 'Vượt mục tiêu — thưởng 2.000.000đ'],
                'EMP008' => ['score' => 88.0, 'progress' => 100, 'status' => 'completed', 'comment' => 'Khá — thưởng 700.000đ'],
            ],
            9 => [
                'EMP003' => ['score' => 100.0, 'progress' => 100, 'status' => 'completed', 'comment' => 'Hoàn hảo — thưởng 2.000.000đ'],
                'EMP004' => ['score' => 91.0, 'progress' => 100, 'status' => 'completed', 'comment' => 'Tốt — thưởng 1.200.000đ'],
                'EMP005' => ['score' => 78.0, 'progress' => 100, 'status' => 'completed', 'comment' => 'Thưởng 300.000đ'],
                'NV26080008' => ['score' => 82.0, 'progress' => 100, 'status' => 'completed', 'comment' => 'Thưởng 700.000đ'],
                'EMP007' => ['score' => 93.0, 'progress' => 100, 'status' => 'completed', 'comment' => 'Thưởng 1.200.000đ'],
                'EMP008' => ['score' => 71.0, 'progress' => 100, 'status' => 'completed', 'comment' => 'Thưởng 300.000đ'],
            ],
            10 => [
                'EMP003' => ['score' => 95.0, 'progress' => 100, 'status' => 'completed', 'comment' => 'Thưởng 1.200.000đ'],
                'EMP004' => ['score' => 86.0, 'progress' => 100, 'status' => 'completed', 'comment' => 'Thưởng 700.000đ'],
                'EMP005' => ['score' => 72.0, 'progress' => 100, 'status' => 'completed', 'comment' => 'Thưởng 300.000đ'],
                'NV26080008' => ['score' => 100.0, 'progress' => 100, 'status' => 'completed', 'comment' => 'Thưởng 2.000.000đ'],
                'EMP007' => ['score' => 89.0, 'progress' => 100, 'status' => 'completed', 'comment' => 'Thưởng 700.000đ'],
                'EMP008' => ['score' => 68.0, 'progress' => 100, 'status' => 'completed', 'comment' => 'Không đạt — 0đ thưởng'],
            ],
            11 => [
                'EMP003' => ['score' => 100.0, 'progress' => 100, 'status' => 'completed', 'comment' => 'Thưởng 2.000.000đ'],
                'EMP004' => ['score' => 83.0, 'progress' => 100, 'status' => 'completed', 'comment' => 'Thưởng 700.000đ'],
                'EMP005' => ['score' => null, 'progress' => 55, 'status' => 'in_progress', 'comment' => 'Đang thực hiện — chờ manager chấm điểm'],
                'NV26080008' => ['score' => 92.0, 'progress' => 100, 'status' => 'completed', 'comment' => 'Thưởng 1.200.000đ'],
                'EMP007' => ['score' => null, 'progress' => 40, 'status' => 'pending', 'comment' => 'Chờ bắt đầu — manager giao KPI'],
                'EMP008' => ['score' => 77.0, 'progress' => 100, 'status' => 'completed', 'comment' => 'Thưởng 300.000đ'],
            ],
        ];

        foreach ($matrix as $month => $employees) {
            foreach ($employees as $empCode => $data) {
                $employeeId = $emp($empCode);
                if (! $employeeId) {
                    continue;
                }

                $isIt = str_starts_with($empCode, 'EMP') && in_array($empCode, ['EMP003', 'EMP004', 'EMP005'], true)
                    || $empCode === 'NV26080008'
                    || str_starts_with($empCode, 'LEAVE30');
                $isSale = in_array($empCode, ['EMP007', 'EMP008'], true);

                if ($isIt) {
                    $kpiCode = sprintf('KPI-DEMO-IT-%02d', $month);
                } elseif ($isSale) {
                    $kpiCode = sprintf('KPI-DEMO-SA-%02d', $month);
                } else {
                    continue;
                }

                if (! isset($this->kpiIds[$kpiCode], $this->assignmentIds[$kpiCode])) {
                    continue;
                }

                DB::table('employee_kpis')->insert([
                    'assignment_id' => $this->assignmentIds[$kpiCode],
                    'employee_id' => $employeeId,
                    'kpi_id' => $this->kpiIds[$kpiCode],
                    'target' => 'Hoàn thành ≥100% mục tiêu tháng '.str_pad((string) $month, 2, '0', STR_PAD_LEFT).'/2026',
                    'deadline' => Carbon::create(2026, $month, 1)->endOfMonth()->toDateString(),
                    'progress' => $data['progress'],
                    'status' => $data['status'],
                    'score' => $data['score'],
                    'comment' => $data['comment'],
                    'review' => $data['score'] !== null ? 'Đánh giá demo KPI tháng '.$month.'/2026' : null,
                    'assigned_by' => str_starts_with($kpiCode, 'KPI-DEMO-IT')
                        ? $this->itManagerUserId
                        : $this->saleManagerUserId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * @param  list<int>  $months
     */
    private function recalculatePayrollBonuses(array $months, int $year): void
    {
        $payrollService = app(PayrollService::class);

        foreach ($months as $month) {
            $period = PayrollPeriod::query()->where('month', $month)->where('year', $year)->first();
            if (! $period) {
                continue;
            }

            Payroll::withTrashed()->where('payroll_period_id', $period->id)->forceDelete();
            $result = $payrollService->calculatePayrollForPeriod($period);
            if ($result === 'success' && $period->status !== 'paid' && $period->status !== 'closed') {
                $period->update(['status' => 'calculated']);
            }

            $this->command?->line("  ↳ Tính lại lương tháng {$month}/{$year} (cập nhật cột Thưởng KPI)");
        }
    }

    private function printSummary(): void
    {
        $bonusTiers = [
            ['Mức điểm', 'Thưởng KPI'],
            ['< 70%', '0đ'],
            ['70% – 79%', '300.000đ'],
            ['80% – 89%', '700.000đ'],
            ['90% – 99%', '1.200.000đ'],
            ['≥ 100%', '2.000.000đ'],
        ];

        $this->command?->info('Hoàn tất dữ liệu mẫu KPI & thưởng!');
        $this->command?->table(['Mức điểm', 'Thưởng KPI'], array_slice($bonusTiers, 1));

        $rows = [];
        foreach ([10, 11] as $month) {
            $period = PayrollPeriod::query()->where('month', $month)->where('year', 2026)->first();
            if (! $period) {
                continue;
            }

            $samples = Payroll::query()
                ->with('employee')
                ->where('payroll_period_id', $period->id)
                ->where('bonus', '>', 0)
                ->orderByDesc('bonus')
                ->limit(3)
                ->get();

            foreach ($samples as $payroll) {
                $rows[] = [
                    str_pad((string) $month, 2, '0', STR_PAD_LEFT).'/2026',
                    $payroll->employee?->employee_code ?? '—',
                    number_format((float) $payroll->bonus, 0, ',', '.').'đ',
                ];
            }
        }

        if ($rows !== []) {
            $this->command?->table(['Kỳ lương', 'Mã NV', 'Thưởng KPI'], $rows);
        }

        $this->command?->info('KPI demo: KPI-DEMO-IT-08..11, KPI-DEMO-SA-08..11');
        $this->command?->info('Cách test:');
        $this->command?->line('  • Admin → KPI / Giao KPI: xem KPI tháng 8–11/2026');
        $this->command?->line('  • Manager → KPI: chấm điểm EMP005 (tháng 11) và EMP007 (tháng 11) đang chờ');
        $this->command?->line('  • Kế toán → Kỳ lương → xem cột Thưởng (KPI) tháng 10/11');
        $this->command?->line('  • Employee → KPI: xem điểm và nhận xét của mình');
    }
}
