<?php

namespace App\Support;

use App\Models\LeaveRequest;

/**
 * Quy tắc chặn tăng ca khi nhân viên đang nghỉ phép / ốm / thai sản / không lương.
 */
final class OvertimeLeaveConflictRules
{
    /**
     * Loại nghỉ khiến không được tạo OT trùng khoảng nghỉ.
     *
     * @var list<string>
     */
    public const BLOCKING_LEAVE_TYPES = [
        'annual',
        'sick',
        'maternity',
        'unpaid',
        'half_day',
    ];

    /**
     * @return list<string>
     */
    public static function blockingLeaveTypes(): array
    {
        return self::BLOCKING_LEAVE_TYPES;
    }

    /**
     * @return array{0: string, 1: string} [start, end] dạng HH:MM
     */
    public static function halfDayWindow(string $period): array
    {
        $windows = config('overtime.half_day_leave_windows', []);

        $window = $windows[$period] ?? null;

        if (! is_array($window) || count($window) !== 2) {
            return match ($period) {
                LeaveRequest::HALF_DAY_MORNING => ['08:00', '12:00'],
                LeaveRequest::HALF_DAY_AFTERNOON => ['13:00', '17:00'],
                default => ['00:00', '23:59'],
            };
        }

        return [
            TimeInput::forInput($window[0]),
            TimeInput::forInput($window[1]),
        ];
    }
}
