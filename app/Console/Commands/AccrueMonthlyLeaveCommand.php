<?php

namespace App\Console\Commands;

use App\Services\LeaveMonthlyAccrualService;
use Illuminate\Console\Command;

class AccrueMonthlyLeaveCommand extends Command
{
    protected $signature = 'leave:accrue-monthly
                            {--year= : Năm cộng phép (mặc định: tháng đích theo lịch)}
                            {--month= : Tháng cộng phép 1-12}
                            {--force : Chạy ngay cả khi chưa phải cuối tháng}';

    protected $description = 'Cuối tháng cộng +1 ngày phép năm cho nhân viên đủ điều kiện (idempotent)';

    public function handle(LeaveMonthlyAccrualService $accrualService): int
    {
        [$year, $month] = $this->resolveTargetPeriod();

        if (! $this->option('force') && ! $this->option('year') && ! $this->option('month')) {
            if (! now()->isLastOfMonth()) {
                $this->comment('Chưa phải cuối tháng — bỏ qua. Dùng --force hoặc --year/--month để chạy thủ công.');

                return self::SUCCESS;
            }
        }

        $this->info("Bắt đầu cộng phép tháng {$month}/{$year}...");

        $result = $accrualService->accrueForMonth($year, $month);

        $this->info($result['run']->message ?? 'Hoàn tất.');
        $this->line("Run #{$result['run']->id}: tạo mới {$result['created']}, bỏ qua trùng {$result['skipped']}, không đủ điều kiện {$result['ineligible']}, chặn nghỉ không lương {$result['blocked_unpaid']}, chặn nghỉ ốm dài {$result['blocked_sick']}.");

        return self::SUCCESS;
    }

    /** @return array{0: int, 1: int} */
    private function resolveTargetPeriod(): array
    {
        if ($this->option('year') && $this->option('month')) {
            return [(int) $this->option('year'), (int) $this->option('month')];
        }

        $target = now();

        if ($target->isLastOfMonth()) {
            return [(int) $target->year, (int) $target->month];
        }

        $previous = $target->copy()->subMonthNoOverflow();

        return [(int) $previous->year, (int) $previous->month];
    }
}
