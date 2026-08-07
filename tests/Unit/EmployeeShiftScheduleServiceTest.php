<?php

use App\Services\EmployeeShiftScheduleService;

test('weekday pattern labels recognize common schedules', function () {
    $service = new EmployeeShiftScheduleService;

    expect($service->weekdayPatternLabel([1, 3, 5]))->toBe('T2, 4, 6')
        ->and($service->weekdayPatternLabel([2, 4, 6]))->toBe('T3, 5, 7')
        ->and($service->weekdayPatternLabel([1, 2, 3, 4, 5, 6]))->toBe('T2 – T7');
});
