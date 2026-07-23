<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CitizenIdNumber implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $digits = self::normalize($value);

        if ($digits === '') {
            $fail('Vui lòng nhập số CCCD/CMND.');

            return;
        }

        if (strlen($digits) === 12) {
            if (! self::isValidCccd($digits)) {
                $fail('Số CCCD không hợp lệ (12 chữ số, mã tỉnh/thành từ 001–096).');
            }

            return;
        }

        if (strlen($digits) === 9 && ctype_digit($digits)) {
            return;
        }

        $fail('Số CCCD phải gồm 12 chữ số (CCCD gắn chip) hoặc 9 chữ số (CMND cũ).');
    }

    public static function normalize(mixed $value): string
    {
        return preg_replace('/\D+/', '', (string) $value) ?? '';
    }

    public static function isValidCccd(string $digits): bool
    {
        if (strlen($digits) !== 12 || ! ctype_digit($digits)) {
            return false;
        }

        $province = (int) substr($digits, 0, 3);

        return $province >= 1 && $province <= 96;
    }
}
