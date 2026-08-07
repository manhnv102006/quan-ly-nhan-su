<?php

use App\Services\EmployeeAttendanceService;
use Carbon\Carbon;

test('early minutes grace excludes first twenty minutes before shift end', function () {
    $service = new EmployeeAttendanceService(app(\App\Services\OvertimeSettlementService::class));
    $sessionEnd = Carbon::parse('2026-08-07 17:00:00');

    expect($service->calculateEarlyMinutes(Carbon::parse('2026-08-07 16:45:00'), $sessionEnd))->toBe(0)
        ->and($service->calculateEarlyMinutes(Carbon::parse('2026-08-07 16:00:00'), $sessionEnd))->toBe(40)
        ->and($service->calculateEarlyMinutes(Carbon::parse('2026-08-07 17:00:00'), $sessionEnd))->toBe(0);
});
