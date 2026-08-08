@php
    $period = $complaint->payroll?->payrollPeriod;
    $fmt = fn ($n) => number_format((float) $n, 0, ',', '.');
@endphp

<div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
    <div class="rounded-2xl border border-slate-100 bg-slate-50/60 p-5 space-y-3">
        <h3 class="text-sm font-bold text-slate-800">Thông tin khiếu nại</h3>
        <div class="grid grid-cols-2 gap-3 text-sm">
            <div><p class="text-xs text-slate-400">Mã</p><p class="font-semibold">{{ $complaint->complaint_code }}</p></div>
            <div><p class="text-xs text-slate-400">Trạng thái</p>@include('payroll-complaints.partials.status-badge', ['complaint' => $complaint])</div>
            <div><p class="text-xs text-slate-400">Loại</p><p class="font-medium">{{ $complaint->issueTypeLabel() }}</p></div>
            <div><p class="text-xs text-slate-400">Kỳ lương</p><p class="font-medium">{{ $period?->name ?? '—' }} ({{ str_pad((string) ($period?->month ?? 0), 2, '0', STR_PAD_LEFT) }}/{{ $period?->year ?? '—' }})</p></div>
            <div class="col-span-2"><p class="text-xs text-slate-400">Tiêu đề</p><p class="font-semibold text-slate-800">{{ $complaint->subject }}</p></div>
            @if($complaint->disputed_amount)
                <div><p class="text-xs text-slate-400">Số tiền chênh lệch</p><p class="font-bold text-rose-600">{{ $fmt($complaint->disputed_amount) }} ₫</p></div>
            @endif
        </div>
        <div>
            <p class="text-xs text-slate-400 mb-1">Mô tả chi tiết</p>
            <p class="text-sm text-slate-700 whitespace-pre-line">{{ $complaint->description }}</p>
        </div>
    </div>

    <div class="rounded-2xl border border-slate-100 bg-white p-5 space-y-3">
        <h3 class="text-sm font-bold text-slate-800">Phiếu lương liên quan</h3>
        @if($complaint->payroll)
            <div class="grid grid-cols-2 gap-3 text-sm">
                <div><p class="text-xs text-slate-400">Lương cơ bản</p><p class="font-medium">{{ $fmt($complaint->payroll->basic_salary) }} ₫</p></div>
                <div><p class="text-xs text-slate-400">Thưởng KPI</p><p class="font-medium">{{ $fmt($complaint->payroll->bonus) }} ₫</p></div>
                <div><p class="text-xs text-slate-400">Khấu trừ</p><p class="font-medium text-rose-600">{{ $fmt($complaint->payroll->deduction) }} ₫</p></div>
                <div><p class="text-xs text-slate-400">Thực lĩnh</p><p class="font-bold text-emerald-700">{{ $fmt($complaint->payroll->total_salary) }} ₫</p></div>
                <div><p class="text-xs text-slate-400">Ngày công</p><p class="font-medium">{{ $complaint->payroll->actual_working_days }}/{{ $complaint->payroll->standard_working_days }}</p></div>
            </div>
        @endif

        @if($complaint->manager_note)
            <div class="rounded-xl bg-sky-50 border border-sky-100 p-3 text-sm">
                <p class="text-xs font-bold text-sky-700">Ghi chú quản lý</p>
                <p class="mt-1 text-sky-900 whitespace-pre-line">{{ $complaint->manager_note }}</p>
                @if($complaint->manager_confirmed_at)
                    <p class="mt-1 text-xs text-sky-600">{{ $complaint->manager_confirmed_at->format('d/m/Y H:i') }}</p>
                @endif
            </div>
        @endif

        @if($complaint->resolution_note)
            <div class="rounded-xl bg-emerald-50 border border-emerald-100 p-3 text-sm">
                <p class="text-xs font-bold text-emerald-700">Kết quả xử lý (kế toán)</p>
                @if($complaint->confirmed_adjustment_amount)
                    <p class="mt-2 text-sm font-bold text-emerald-800">
                        Bổ sung chuyển tháng sau: {{ $fmt($complaint->confirmed_adjustment_amount) }} ₫
                    </p>
                @endif
                <p class="mt-1 text-emerald-900 whitespace-pre-line">{{ $complaint->resolution_note }}</p>
                @if($complaint->resolved_at)
                    <p class="mt-1 text-xs text-emerald-600">{{ $complaint->resolved_at->format('d/m/Y H:i') }}</p>
                @endif
                @if($complaint->isCarried() && $complaint->carriedToPayroll?->payrollPeriod)
                    <p class="mt-2 text-xs font-semibold text-emerald-700">
                        ✓ Đã cộng vào bảng lương {{ $complaint->carriedToPayroll->payrollPeriod->name }}
                        ({{ str_pad((string) $complaint->carriedToPayroll->payrollPeriod->month, 2, '0', STR_PAD_LEFT) }}/{{ $complaint->carriedToPayroll->payrollPeriod->year }})
                        @if($complaint->carried_at)
                            · {{ $complaint->carried_at->format('d/m/Y H:i') }}
                        @endif
                    </p>
                @elseif($complaint->awaitsCarryForward())
                    @php
                        $next = app(\App\Services\PayrollComplaintService::class)->nextPeriodAfter($complaint->payroll->payrollPeriod);
                    @endphp
                    <p class="mt-2 text-xs font-semibold text-amber-700">
                        Chờ cộng vào bảng lương tháng {{ str_pad((string) $next['month'], 2, '0', STR_PAD_LEFT) }}/{{ $next['year'] }} khi kế toán tính lương.
                    </p>
                @endif
            </div>
        @endif

        @if($complaint->reject_reason)
            <div class="rounded-xl bg-rose-50 border border-rose-100 p-3 text-sm">
                <p class="text-xs font-bold text-rose-700">Lý do từ chối</p>
                <p class="mt-1 text-rose-900 whitespace-pre-line">{{ $complaint->reject_reason }}</p>
                @if($complaint->rejected_at)
                    <p class="mt-1 text-xs text-rose-600">{{ $complaint->rejected_at->format('d/m/Y H:i') }}</p>
                @endif
            </div>
        @endif
    </div>
</div>
