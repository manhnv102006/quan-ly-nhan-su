@php
    $limits = $overtimeLimits ?? null;
    $ratioPercent = (int) round(\App\Support\OvertimeLimitRules::dailyOvertimeRatio() * 100);
@endphp

<div class="rounded-xl bg-slate-50 border border-slate-100 px-4 py-3 text-xs text-slate-500 leading-relaxed">
    <p class="font-semibold text-slate-600 mb-1">Giới hạn tăng ca theo Luật Lao động:</p>
    <ul class="list-disc ps-4 space-y-0.5">
        @if ($limits)
            <li>
                Ngày này: giờ làm bình thường {{ rtrim(rtrim(number_format($limits['normal_hours'], 2, '.', ''), '0'), '.') }}h
                → tối đa {{ rtrim(rtrim(number_format($limits['max_daily_overtime'], 2, '.', ''), '0'), '.') }}h tăng ca
                ({{ $ratioPercent }}% giờ làm bình thường).
            </li>
        @else
            <li>Tối đa {{ $ratioPercent }}% giờ làm bình thường trong ngày (ví dụ: 8h → 4h, 6h → 3h).</li>
        @endif
        <li>Tổng giờ làm + tăng ca không quá {{ rtrim(rtrim(number_format($limits['max_total_hours_per_day'] ?? \App\Support\OvertimeLimitRules::maxTotalHoursPerDay(), 2, '.', ''), '0'), '.') }}h/ngày.</li>
        <li>
            Tối đa {{ rtrim(rtrim(number_format($limits['max_monthly_overtime'] ?? \App\Support\OvertimeLimitRules::maxHoursPerMonth(), 2, '.', ''), '0'), '.') }} giờ/tháng
            @if ($limits && array_key_exists('remaining_monthly_hours', $limits))
                — còn {{ rtrim(rtrim(number_format($limits['remaining_monthly_hours'], 2, '.', ''), '0'), '.') }} giờ tháng này.
            @else
                .
            @endif
        </li>
        <li>
            Tối đa {{ rtrim(rtrim(number_format($limits['max_annual_overtime'] ?? \App\Support\OvertimeLimitRules::maxHoursPerYear(), 2, '.', ''), '0'), '.') }} giờ/năm
            @if (($limits['extended_annual_limit'] ?? \App\Support\OvertimeLimitRules::usesExtendedAnnualLimit()))
                (ngành đặc thù)
            @endif
            @if ($limits && array_key_exists('remaining_annual_hours', $limits))
                — còn {{ rtrim(rtrim(number_format($limits['remaining_annual_hours'], 2, '.', ''), '0'), '.') }} giờ năm nay.
            @else
                .
            @endif
        </li>
    </ul>
</div>
