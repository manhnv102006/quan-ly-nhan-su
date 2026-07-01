<?php

namespace App\Support;

use Carbon\Carbon;
use InvalidArgumentException;

class TimeInput
{
    public static function forInput(mixed $time): string
    {
        if ($time === null || $time === '') {
            return '';
        }

        if ($time instanceof Carbon) {
            return $time->format('H:i');
        }

        $value = trim((string) $time);

        if (preg_match('/^\d{2}:\d{2}$/', $value)) {
            return $value;
        }

        if (preg_match('/^(\d{2}:\d{2}):\d{2}$/', $value, $matches)) {
            return $matches[1];
        }

        if (preg_match('/^(\d{1,2}):(\d{2})\s*(AM|PM)$/i', $value, $matches)) {
            $hour = (int) $matches[1];
            $minute = $matches[2];
            $meridiem = strtoupper($matches[3]);

            if ($meridiem === 'PM' && $hour < 12) {
                $hour += 12;
            } elseif ($meridiem === 'AM' && $hour === 12) {
                $hour = 0;
            }

            return sprintf('%02d:%s', $hour, $minute);
        }

        try {
            return Carbon::parse($value)->format('H:i');
        } catch (InvalidArgumentException) {
            return $value;
        }
    }

    public static function normalize(mixed $time): ?string
    {
        if ($time === null || $time === '') {
            return null;
        }

        $formatted = self::forInput($time);

        if (! preg_match('/^\d{2}:\d{2}$/', $formatted)) {
            throw new InvalidArgumentException('Invalid time value.');
        }

        return $formatted;
    }
}
