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
                <h3 class="text-sm font-bold text-emerald-800">Xác nhận công ty tính sai — chuyển bổ sung tháng sau</h3>
                <p class="text-xs text-slate-500">
                    Nhập số tiền công ty đã tính thiếu cho nhân viên. Khoản này sẽ tự động cộng vào bảng lương
                    @if($nextPeriod ?? null)
                        <strong>tháng {{ str_pad((string) $nextPeriod['month'], 2, '0', STR_PAD_LEFT) }}/{{ $nextPeriod['year'] }}</strong>
                    @else
                        <strong>tháng liền sau</strong>
                    @endif
                    khi kế toán tính lương kỳ đó.
                </p>
                <div>
                    <label class="accountant-label">Số tiền bổ sung (₫) <span class="text-rose-500">*</span></label>
                    @php
                        $adjustmentRaw = old('confirmed_adjustment_amount', $payrollComplaint->disputed_amount);
                        $adjustmentDisplay = filled($adjustmentRaw)
                            ? number_format((float) $adjustmentRaw, 0, ',', '.')
                            : '';
                    @endphp
                    <input type="text" name="confirmed_adjustment_amount" required
                           inputmode="numeric" autocomplete="off"
                           value="{{ $adjustmentDisplay }}"
                           class="accountant-field money-input" placeholder="VD: 500.000">
                    @error('confirmed_adjustment_amount')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                    @if($payrollComplaint->disputed_amount)
                        <p class="mt-1 text-xs text-slate-400">Nhân viên khai báo chênh lệch: {{ number_format((float) $payrollComplaint->disputed_amount, 0, ',', '.') }} ₫</p>
                    @endif
                </div>
                <div>
                    <label class="accountant-label">Ghi chú xử lý <span class="text-rose-500">*</span></label>
                    <textarea name="resolution_note" rows="4" required maxlength="2000" class="accountant-field" placeholder="Mô tả sai sót và cách bổ sung...">{{ old('resolution_note') }}</textarea>
                    @error('resolution_note')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit" class="accountant-btn-primary">Xác nhận &amp; chuyển sang tháng sau</button>
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

@include('partials.money-input-script')
</x-accountant-layout>
