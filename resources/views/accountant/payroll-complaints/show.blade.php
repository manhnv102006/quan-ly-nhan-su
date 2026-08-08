<x-accountant-layout title="Chi tiết khiếu nại" subtitle="{{ $payrollComplaint->complaint_code }}">
<div class="accountant-page space-y-6">
    <a href="{{ route('accountant.payroll-complaints.index') }}" class="text-sm font-semibold text-emerald-700 hover:underline">← Danh sách khiếu nại</a>
    @if (session('success'))<div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>@endif
    @if (session('error'))<div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">{{ session('error') }}</div>@endif

    <div class="accountant-card p-4 text-sm">
        <p class="font-semibold text-slate-800">{{ $payrollComplaint->employee?->full_name }} · {{ $payrollComplaint->employee?->employee_code }}</p>
        <p class="text-slate-500">{{ $payrollComplaint->employee?->department?->department_name }}</p>
    </div>

    @include('payroll-complaints.partials.detail', ['complaint' => $payrollComplaint])

    @if($payrollComplaint->isProcessing())
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <form method="POST" action="{{ route('accountant.payroll-complaints.resolve', $payrollComplaint) }}" class="accountant-card p-5 space-y-3">
                @csrf @method('PATCH')
                <h3 class="text-sm font-bold text-emerald-800">Đánh dấu đã xử lý</h3>
                <p class="text-xs text-slate-500">Ghi rõ đã kiểm tra, điều chỉnh lương hoặc giải thích cho nhân viên.</p>
                <textarea name="resolution_note" rows="4" required maxlength="2000" class="accountant-field">{{ old('resolution_note') }}</textarea>
                <button type="submit" class="accountant-btn-primary">Hoàn tất xử lý</button>
            </form>
            <form method="POST" action="{{ route('accountant.payroll-complaints.reject', $payrollComplaint) }}" class="accountant-card p-5 space-y-3 border-rose-100">
                @csrf @method('PATCH')
                <h3 class="text-sm font-bold text-rose-800">Từ chối khiếu nại</h3>
                <textarea name="reject_reason" rows="4" required maxlength="1000" class="accountant-field" placeholder="Lý do từ chối...">{{ old('reject_reason') }}</textarea>
                <button type="submit" class="rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-rose-700">Từ chối</button>
            </form>
        </div>
    @elseif($payrollComplaint->isPending())
        <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            Khiếu nại đang chờ quản lý phòng ban xác nhận trước khi chuyển kế toán xử lý.
        </div>
    @endif
</div>
</x-accountant-layout>
