@php
    $balance = $leaveBalance ?? null;
    $formatDays = static function (float $days): string {
        $formatted = fmod($days, 1.0) === 0.0
            ? (string) (int) $days
            : number_format($days, 1, ',', '');

        return $formatted.' ngày';
    };
@endphp

@if($balance)
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <div class="rounded-3xl border border-emerald-200 bg-emerald-50/80 p-5">
            <p class="text-[11px] font-bold uppercase tracking-wide text-emerald-800">Hưởng lương tháng {{ $balance['month_label'] }}</p>
            <p class="mt-2 text-3xl font-black text-emerald-900">{{ $formatDays($balance['monthly_remaining']) }}</p>
            <p class="mt-1 text-xs text-emerald-800">
                Còn lại / hạn mức {{ $formatDays($balance['monthly_quota']) }}
            </p>
            <p class="mt-3 text-xs leading-relaxed text-emerald-900/80">
                Đã duyệt: <strong>{{ $formatDays($balance['monthly_used']) }}</strong>
                @if($balance['monthly_pending'] > 0)
                    · Đang chờ: <strong>{{ $formatDays($balance['monthly_pending']) }}</strong>
                @endif
            </p>
            <p class="mt-2 text-[11px] leading-relaxed text-emerald-800/80">
                @if($balance['monthly_quota'] <= 0)
                    Chưa đủ điều kiện: cần hoàn thành <strong>trọn 1 tháng làm việc</strong> mới được 1 ngày hưởng lương/tháng. {{ \App\Support\LeaveAccrualRules::proRataDescription() }}
                @else
                    Mọi loại hưởng lương (phép, ốm, nửa ngày…) cùng tính vào hạn mức 1 ngày/tháng. Vượt hạn mức sẽ trừ 300.000 ₫/ngày.
                @endif
            </p>
        </div>

        <div class="rounded-3xl border border-sky-200 bg-sky-50/80 p-5">
            <p class="text-[11px] font-bold uppercase tracking-wide text-sky-800">Phép năm {{ $balance['year'] }}</p>
            <p class="mt-2 text-3xl font-black text-sky-900">{{ $formatDays($balance['annual_remaining']) }}</p>
            <p class="mt-1 text-xs text-sky-800">
                Năm {{ $balance['year'] }}: {{ $formatDays($balance['current_year_remaining'] ?? max(0, $balance['annual_quota'] - $balance['annual_used'])) }} còn / {{ $formatDays($balance['annual_quota']) }} hạn mức
                @if(($balance['carried_over_remaining'] ?? 0) > 0)
                    · Chuyển từ {{ $balance['carried_over']['source_year'] ?? 'năm trước' }}: {{ $formatDays($balance['carried_over_remaining']) }}
                @endif
            </p>
            <p class="mt-3 text-xs leading-relaxed text-sky-900/80">
                Đã duyệt: <strong>{{ $formatDays($balance['annual_used']) }}</strong>
                @if($balance['annual_pending'] > 0)
                    · Đang chờ: <strong>{{ $formatDays($balance['annual_pending']) }}</strong>
                @endif
            </p>
            <p class="mt-2 text-[11px] leading-relaxed text-sky-800/80">
                Chỉ tính loại Nghỉ phép. Đơn vừa gửi đã trừ số còn lại; quản lý duyệt thì giữ nguyên, từ chối hoặc hủy thì hoàn lại. Muốn hưởng lương vẫn phải nằm trong 1 ngày/tháng.
                @if($balance['annual_is_prorated'] ?? false)
                    Hạn mức năm nay cộng dồn <strong>1 ngày/tháng</strong> (pro-rata). {{ \App\Support\LeaveAccrualRules::proRataDescription() }}
                @endif
                @if(($balance['carried_over']['active'] ?? false) && ($balance['carried_over_remaining'] ?? 0) > 0)
                    Phép chuyển từ năm {{ $balance['carried_over']['source_year'] }} dùng trước, hết hạn <strong>{{ \Illuminate\Support\Carbon::parse($balance['carried_over']['expires_at'])->format('d/m/Y') }}</strong>.
                @endif
            </p>
        </div>
    </div>
@endif
