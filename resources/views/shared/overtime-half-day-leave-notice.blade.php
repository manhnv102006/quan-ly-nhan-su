@php
    $halfDay = $halfDayLeaveContext ?? null;
@endphp

@if ($halfDay)
    <div class="rounded-2xl border border-violet-200 bg-violet-50 px-5 py-4 shadow-sm">
        <p class="text-sm font-semibold text-violet-950">Nghỉ nửa ngày — chỉ OT ngoài giờ nghỉ</p>
        <p class="mt-2 text-xs leading-relaxed text-violet-900">
            Bạn đã nghỉ <strong>{{ $halfDay['period_label'] }}</strong>
            ({{ $halfDay['blocked_start'] }}–{{ $halfDay['blocked_end'] }}) ngày này
            @if (($halfDay['status'] ?? '') === 'pending')
                (đơn chờ duyệt).
            @else
                .
            @endif
            Chỉ được đăng ký tăng ca <strong>ngoài</strong> khung {{ $halfDay['blocked_start'] }}–{{ $halfDay['blocked_end'] }}.
        </p>
    </div>
@endif
