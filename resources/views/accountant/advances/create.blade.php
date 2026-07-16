<x-accountant-layout title="Tạo tạm ứng" subtitle="Lập yêu cầu ứng lương cho nhân viên">
    @include('accountant.advances.partials.sub-nav', ['active' => 'requests'])
    <div class="accountant-page max-w-xl">
        <a href="{{ route('accountant.advances.index') }}" class="text-sm font-semibold text-cyan-700 hover:underline">← Danh sách</a>
        <h2 class="mt-2 text-2xl font-bold text-slate-900">Tạo yêu cầu tạm ứng</h2>

        <form method="POST" action="{{ route('accountant.advances.store') }}" class="accountant-card mt-5 space-y-4 p-6">
            @csrf
            <div>
                <label class="accountant-label">Nhân viên *</label>
                <select name="employee_id" required class="accountant-field">
                    <option value="">-- Chọn --</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" @selected(old('employee_id') == $emp->id)>{{ $emp->full_name }} ({{ $emp->employee_code }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="accountant-label">Số tiền ứng *</label>
                <input type="text" name="amount" inputmode="numeric" required
                       value="{{ old('amount') ? number_format((float) old('amount'), 0, ',', '.') : '' }}"
                       class="accountant-field money-input" placeholder="VD: 5.000.000">
                <p class="mt-1 text-xs text-slate-400">Tối thiểu {{ number_format(\App\Models\SalaryAdvance::MIN_AMOUNT, 0, ',', '.') }}₫</p>
            </div>
            <div>
                <label class="accountant-label">Ngày yêu cầu *</label>
                <input type="date" name="request_date" required value="{{ old('request_date', now()->format('Y-m-d')) }}" class="accountant-field">
            </div>
            <div>
                <label class="accountant-label">Lý do *</label>
                <textarea name="reason" required rows="3" class="accountant-field">{{ old('reason') }}</textarea>
            </div>
            <div>
                <label class="accountant-label">Ghi chú</label>
                <textarea name="note" rows="2" class="accountant-field">{{ old('note') }}</textarea>
            </div>
            <button type="submit" class="accountant-btn-primary">Gửi yêu cầu</button>
        </form>
    </div>

    @include('partials.money-input-script')
</x-accountant-layout>
