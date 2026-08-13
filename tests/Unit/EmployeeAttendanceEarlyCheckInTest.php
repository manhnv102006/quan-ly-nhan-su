<?php

use App\Models\Attendance;
use App\Models\Shift;
use App\Services\EmployeeAttendanceService;
use Carbon\Carbon;
use Tests\TestCase;

uses(TestCase::class);

test('employee can check in up to one hour before shift start', function () {
    $service = app(EmployeeAttendanceService::class);
    $shift = new Shift;
    $shift->setRawAttributes([
        'shift_name' => 'Ca sáng',
        'start_time' => '08:00:00',
        'end_time' => '12:00:00',
    ]);
    $attendance = new Attendance;
    $date = Carbon::parse('2026-08-08');

    $thirtyMinutesEarly = $service->regularSession(
        $attendance,
        $shift,
        $date,
        Carbon::parse('2026-08-08 07:30:00'),
    );

    expect($thirtyMinutesEarly['can_check_in'])->toBeTrue()
        ->and($thirtyMinutesEarly['status_tone'])->toBe('ready')
        ->and($thirtyMinutesEarly['earliest_check_in']->format('H:i'))->toBe('07:00');

    $exactlyOneHourEarly = $service->regularSession(
        $attendance,
        $shift,
        $date,
        Carbon::parse('2026-08-08 07:00:00'),
    );

    expect($exactlyOneHourEarly['can_check_in'])->toBeTrue();

    $tooEarly = $service->regularSession(
        $attendance,
        $shift,
        $date,
        Carbon::parse('2026-08-08 06:59:00'),
    );

    expect($tooEarly['can_check_in'])->toBeFalse()
        ->and($tooEarly['status_tone'])->toBe('upcoming');
});

test('check in one hour early does not count as late', function () {
    $service = app(EmployeeAttendanceService::class);
    $sessionStart = Carbon::parse('2026-08-08 08:00:00');
    $checkIn = Carbon::parse('2026-08-08 07:15:00');

    expect($service->calculateLateMinutes($checkIn, $sessionStart))->toBe(0);
});
