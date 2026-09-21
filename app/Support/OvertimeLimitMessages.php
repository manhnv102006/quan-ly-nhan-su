<?php

namespace App\Support;

final class OvertimeLimitMessages
{
    public static function monthlyApproaching(float $used, float $max, float $threshold): string
    {
        $remaining = max(0, round($max - $used, 2));

        return sprintf(
            'Cảnh báo: đã tích lũy %sh/%sh tăng ca trong tháng (≥%s%% ngưỡng %sh). Còn %s giờ trước khi chạm giới hạn %sh/tháng.',
            self::format($used),
            self::format($max),
            (int) round(OvertimeLimitRules::warningRatio() * 100),
            self::format($threshold),
            self::format($remaining),
            self::format($max),
        );
    }

    public static function yearlyApproaching(float $used, float $max, float $threshold): string
    {
        $remaining = max(0, round($max - $used, 2));
        $extendedSuffix = OvertimeLimitRules::usesExtendedAnnualLimit() ? ' (ngành đặc thù)' : '';

        return sprintf(
            'Cảnh báo: đã tích lũy %sh/%sh tăng ca trong năm%s (≥%s%% ngưỡng %sh). Còn %s giờ trước khi chạm giới hạn %sh/năm.',
            self::format($used),
            self::format($max),
            $extendedSuffix,
            (int) round(OvertimeLimitRules::warningRatio() * 100),
            self::format($threshold),
            self::format($remaining),
            self::format($max),
        );
    }

    public static function laborDepartmentNotification(float $used): string
    {
        return sprintf(
            'Nhắc nhở: nhân viên đã đạt %sh tăng ca/năm (từ %sh). Theo quy định, doanh nghiệp phải thông báo với Sở Lao động – Thương binh và Xã hội (Sở LĐTBXH).',
            self::format($used),
            self::format(OvertimeLimitRules::laborDepartmentNotificationHours()),
        );
    }

    private static function format(float $hours): string
    {
        return rtrim(rtrim(number_format($hours, 2, '.', ''), '0'), '.');
    }
}
