<x-manager-layout title="Chi tiết khiếu nại" subtitle="{{ $payrollComplaint->complaint_code }}">
    <div class="manager-page space-y-6">
        <a href="{{ route('manager.payroll-complaints.index') }}" class="text-sm font-semibold text-teal-700 hover:underline">← Danh sách khiếu nại</a>
        @if (session('success'))<div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-800">{{ session('success') }}</div>@endif
        @if (session('error'))<div class="rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-800">{{ session('error') }}</div>@endif

        <div class="rounded-2xl border border-slate-100 bg-white p-4 text-sm">
            <p class="font-semibold text-slate-800">{{ $payrollComplaint->employee?->full_name }}</p>
            <p class="text-slate-500">{{ $payrollComplaint->employee?->department?->department_name }} · {{ $payrollComplaint->employee?->position?->position_name }}</p>
        </div>

        @include('payroll-complaints.partials.detail', ['complaint' => $payrollComplaint])

        @if($payrollComplaint->isPending())
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <form method="POST" action="{{ route('manager.payroll-complaints.confirm', $payrollComplaint) }}" class="rounded-2xl border border-emerald-200 bg-emerald-50/40 p-5 space-y-3">
                    @csrf @method('PATCH')
                    <h3 class="text-sm font-bold text-emerald-800">Chuyển kế toán xử lý</h3>
                    <textarea name="manager_note" rows="3" maxlength="1000" placeholder="Ghi chú cho kế toán (tuỳ chọn)"
                              class="w-full rounded-xl border border-emerald-200 px-3 py-2 text-sm">{{ old('manager_note') }}</textarea>
                    <button type="submit" class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Xác nhận & chuyển kế toán</button>
                </form>
                <form method="POST" action="{{ route('manager.payroll-complaints.reject', $payrollComplaint) }}" class="rounded-2xl border border-rose-200 bg-rose-50/40 p-5 space-y-3">
                    @csrf @method('PATCH')
                    <h3 class="text-sm font-bold text-rose-800">Từ chối khiếu nại</h3>
                    <textarea name="reject_reason" rows="3" required maxlength="1000" placeholder="Lý do từ chối..."
                              class="w-full rounded-xl border border-rose-200 px-3 py-2 text-sm">{{ old('reject_reason') }}</textarea>
                    <button type="submit" class="rounded-xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-700">Từ chối</button>
                </form>
            </div>
        @endif
    </div>
</x-manager-layout>
