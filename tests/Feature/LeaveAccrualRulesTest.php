<?php

use App\Support\LeaveAccrualRules;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;

beforeEach(function () {
    Config::set('leave.mid_month_cutoff_day', 15);
    Config::set('leave.mid_month_after_cutoff', LeaveAccrualRules::AFTER_CUTOFF_NEXT_MONTH);
});

test('accrual start month uses hire month when hired on cutoff day', function () {
    expect(LeaveAccrualRules::accrualStartMonth(Carbon::parse('2026-07-15'), 2026))->toBe(7);
});

test('accrual start month shifts to next month after cutoff by default', function () {
    expect(LeaveAccrualRules::accrualStartMonth(Carbon::parse('2026-07-16'), 2026))->toBe(8);
});

test('accrual start month stays on hire month when round month rule is configured', function () {
    Config::set('leave.mid_month_after_cutoff', LeaveAccrualRules::AFTER_CUTOFF_CURRENT_MONTH);

    expect(LeaveAccrualRules::accrualStartMonth(Carbon::parse('2026-07-20'), 2026))->toBe(7);
});

test('december hire after cutoff has no accrual month in same year', function () {
    expect(LeaveAccrualRules::accrualStartMonth(Carbon::parse('2026-12-20'), 2026))->toBeNull();
});
