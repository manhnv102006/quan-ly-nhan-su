@if ($employee->isOvertimeProhibited())
    <div class="rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 shadow-sm">
        <p class="text-sm font-semibold text-rose-950">Cấm tăng ca theo Điều 137 BLLĐ</p>
        <p class="mt-2 text-xs leading-relaxed text-rose-900">
            {{ $employee->overtimeBanStatusLabel() }}. Bạn không thể gửi đơn tăng ca trong thời gian này.
        </p>
    </div>
@endif
