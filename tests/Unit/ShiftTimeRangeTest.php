<?php

use App\Support\ShiftTimeRange;

test('shift time ranges overlap for 7h-9h and 8h-10h', function () {
    expect(ShiftTimeRange::overlaps('07:00:00', '09:00:00', '08:00:00', '10:00:00'))->toBeTrue();
});

test('shift time ranges do not overlap for 8h-12h and 13h-17h', function () {
    expect(ShiftTimeRange::overlaps('08:00:00', '12:00:00', '13:00:00', '17:00:00'))->toBeFalse();
});

test('cross midnight shift overlaps evening shift on same day', function () {
    expect(ShiftTimeRange::overlaps('20:25:00', '21:30:00', '20:38:00', '13:38:00'))->toBeTrue();
});
