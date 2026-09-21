<?php

namespace App\Console\Commands;

use App\Services\LeaveCarryOverService;
use Illuminate\Console\Command;

class CarryOverAnnualLeaveCommand extends Command
{
    protected $signature = 'leave:carry-over-annual
                            {--year= : Năm nguồn cần chuyển phép (mặc định: năm trước)}';

    protected $description = 'Chuyển phép năm còn dư sang năm sau (carried_over), hạn dùng đến hết tháng cấu hình';

    public function handle(LeaveCarryOverService $carryOverService): int
    {
        $sourceYear = (int) ($this->option('year') ?: now()->year - 1);

        $this->info("Chuyển phép dư năm {$sourceYear} → ".($sourceYear + 1).'...');

        $result = $carryOverService->processCarryOverForSourceYear($sourceYear);

        $this->info("Hoàn tất: tạo mới {$result['created']}, bỏ qua {$result['skipped']}, tổng {$result['total_days']} ngày chuyển.");

        return self::SUCCESS;
    }
}
