<?php

namespace App\Support;

use Carbon\Carbon;

class LeaveDateRange
{
    /**
     * Hai khoảng nghỉ [A.start, A.end] và [B.start, B.end] trùng nhau khi:
     * A.start <= B.end AND A.end >= B.start
     */
    public static function periodsOverlap(
        Carbon|string $aStart,
        Carbon|string $aEnd,
        Carbon|string $bStart,
        Carbon|string $bEnd,
    ): bool {
        $aStart = Carbon::parse($aStart)->startOfDay();
        $aEnd = Carbon::parse($aEnd)->startOfDay();
        $bStart = Carbon::parse($bStart)->startOfDay();
        $bEnd = Carbon::parse($bEnd)->startOfDay();

        return $aStart->lte($bEnd) && $aEnd->gte($bStart);
    }

    public static function dayWithinPeriod(
        Carbon|string $day,
        Carbon|string $periodStart,
        Carbon|string $periodEnd,
    ): bool {
        $d = Carbon::parse($day)->startOfDay();
        $start = Carbon::parse($periodStart)->startOfDay();
        $end = Carbon::parse($periodEnd)->startOfDay();

        return $d->gte($start) && $d->lte($end);
    }

    /**
     * @return \Generator<int, Carbon>
     */
    public static function eachCalendarDay(Carbon|string $start, Carbon|string $end): \Generator
    {
        $current = Carbon::parse($start)->startOfDay();
        $last = Carbon::parse($end)->startOfDay();

        while ($current->lte($last)) {
            yield $current->copy();
            $current->addDay();
        }
    }

    public static function formatPeriod(Carbon|string $start, Carbon|string $end): string
    {
        return Carbon::parse($start)->format('d/m/Y').' → '.Carbon::parse($end)->format('d/m/Y');
    }
}
