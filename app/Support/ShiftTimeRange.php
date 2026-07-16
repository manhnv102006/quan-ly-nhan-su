<?php

namespace App\Support;

use Carbon\Carbon;

class ShiftTimeRange
{
    /**
     * Kiểm tra hai khung giờ ca làm có trùng nhau trong cùng một ngày làm việc.
     */
    public static function overlaps(string $startA, string $endA, string $startB, string $endB): bool
    {
        foreach (self::toSameDayIntervals($startA, $endA) as [$aStart, $aEnd]) {
            foreach (self::toSameDayIntervals($startB, $endB) as [$bStart, $bEnd]) {
                if ($aStart < $bEnd && $aEnd > $bStart) {
                    return true;
                }
            }
        }

        return false;
    }

    public static function isValidSameDayRange(string $start, string $end): bool
    {
        return self::toMinutes($start) < self::toMinutes($end);
    }

    /**
     * @return list<array{0: int, 1: int}>
     */
    private static function toSameDayIntervals(string $start, string $end): array
    {
        $startMinutes = self::toMinutes($start);
        $endMinutes = self::toMinutes($end);

        if ($endMinutes > $startMinutes) {
            return [[$startMinutes, $endMinutes]];
        }

        if ($endMinutes === $startMinutes) {
            return [[$startMinutes, $startMinutes + 1]];
        }

        return [
            [$startMinutes, 24 * 60],
            [0, $endMinutes],
        ];
    }

    private static function toMinutes(string $time): int
    {
        $parsed = Carbon::parse($time);

        return ($parsed->hour * 60) + $parsed->minute;
    }
}
