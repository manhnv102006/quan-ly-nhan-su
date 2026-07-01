<?php

namespace App\Console\Commands;

use App\Services\EmployeeAttendanceService;
use Illuminate\Console\Command;

class AutoCheckoutAttendanceCommand extends Command
{
    protected $signature = 'attendance:auto-checkout';

    protected $description = 'Tự động check-out cho nhân viên quá 5 phút sau giờ kết ca';

    public function handle(EmployeeAttendanceService $attendanceService): int
    {
        $count = $attendanceService->processAutoCheckouts();

        $this->info("Đã tự động check-out {$count} bản ghi chấm công.");

        return self::SUCCESS;
    }
}
