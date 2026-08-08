@php
    $isEdit = isset($insurance);
    $rates = $rates ?? [];
    $selectedEmployee = $selectedEmployee ?? null;

    if ($isEdit) {
        $displayRates = [
            'bhxh_employee_rate' => (float) $insurance->bhxh_employee_rate,
            'bhxh_employer_rate' => (float) $insurance->bhxh_employer_rate,
            'bhyt_employee_rate' => (float) $insurance->bhyt_employee_rate,
            'bhyt_employer_rate' => (float) $insurance->bhyt_employer_rate,
            'bhtn_employee_rate' => (float) $insurance->bhtn_employee_rate,
        ];
    } else {
        $displayRates = $rates;
    }

    $rateLabels = [
        'bhxh_employee_rate' => 'BHXH NLĐ',
        'bhxh_employer_rate' => 'BHXH DN',
        'bhyt_employee_rate' => 'BHYT NLĐ',
        'bhyt_employer_rate' => 'BHYT DN',
    ];
    $bhtnPct = round(($displayRates['bhtn_employee_rate'] ?? 0.01) * 100, 2);
@endphp

<div class="grid grid-cols-1 gap-4 md:grid-cols-2">
    @if(!$isEdit)
        @if($selectedEmployee)
            <div class="md:col-span-2 rounded-xl border border-sky-100 bg-sky-50/50 px-4 py-3">
                <p class="text-xs font-bold uppercase text-sky-800">Nhân viên</p>
                <p class="mt-1 font-semibold text-slate-800">{{ $selectedEmployee->full_name }} ({{ $selectedEmployee->employee_code }})</p>
                <input type="hidden" name="employee_id" value="{{ $selectedEmployee->id }}">
            </div>
        @else
            <div class="md:col-span-2">
                <label class="accountant-label">Nhân viên <span class="text-rose-500">*</span></label>
                <select name="employee_id" id="employee_id" required class="accountant-field" onchange="loadSuggestedSalary(this.value)">
                    <option value="">-- Chọn nhân viên --</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" @selected(old('employee_id') == $emp->id)>{{ $emp->full_name }} ({{ $emp->employee_code }})</option>
                    @endforeach
                </select>
            </div>
        @endif
    @endif

    <div>
        <label class="accountant-label">Số sổ BHXH</label>
        <input type="text" name="social_insurance_number" value="{{ old('social_insurance_number', $insurance->social_insurance_number ?? '') }}" class="accountant-field">
    </div>
    <div>
        <label class="accountant-label">Mã BHYT</label>
        <input type="text" name="health_insurance_code" value="{{ old('health_insurance_code', $insurance->health_insurance_code ?? '') }}" class="accountant-field">
    </div>
    <div>
        <label class="accountant-label">Mức lương đóng BH <span class="text-rose-500">*</span></label>
        <input type="number" name="contribution_salary" id="contribution_salary" min="0" step="1000" required
               value="{{ old('contribution_salary', $insurance->contribution_salary ?? '') }}" class="accountant-field">
    </div>
    <div>
        <label class="accountant-label">Ngày bắt đầu <span class="text-rose-500">*</span></label>
        <input type="date" name="start_date" required value="{{ old('start_date', isset($insurance) ? $insurance->start_date?->format('Y-m-d') : now()->format('Y-m-d')) }}" class="accountant-field">
    </div>

    @if($isEdit)
        <div>
            <label class="accountant-label">Trạng thái</label>
            <select name="status" class="accountant-field">
                @foreach(\App\Models\EmployeeInsurance::STATUS_LABELS as $val => $label)
                    <option value="{{ $val }}" @selected(old('status', $insurance->status) === $val)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="accountant-label">Ngày kết thúc</label>
            <input type="date" name="end_date" value="{{ old('end_date', $insurance->end_date?->format('Y-m-d')) }}" class="accountant-field">
        </div>
    @endif
</div>

<div class="mt-4 rounded-2xl border border-sky-100 bg-sky-50/40 p-4">
    <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
        <p class="text-xs font-bold uppercase text-sky-800">Tỷ lệ đóng bảo hiểm (%)</p>
        <a href="{{ route('accountant.insurance.rates.index') }}" class="text-xs font-semibold text-sky-700 hover:underline">Quản lý tỷ lệ →</a>
    </div>
    <div class="grid grid-cols-2 gap-3 md:grid-cols-3">
        @foreach($rateLabels as $field => $label)
            <div class="rounded-xl border border-sky-100 bg-white/80 px-3 py-2.5">
                <p class="text-[10px] font-semibold uppercase text-slate-500">{{ $label }}</p>
                <p class="mt-0.5 text-sm font-bold text-slate-800">{{ number_format(round(($displayRates[$field] ?? 0) * 100, 2), 2, ',', '.') }}%</p>
            </div>
        @endforeach
        <div class="rounded-xl border border-sky-100 bg-white/80 px-3 py-2.5">
            <p class="text-[10px] font-semibold uppercase text-slate-500">BHTN (NLĐ &amp; DN)</p>
            <p class="mt-0.5 text-sm font-bold text-slate-800">{{ number_format($bhtnPct, 2, ',', '.') }}%</p>
        </div>
    </div>
    <p class="mt-2 text-xs text-slate-500">
        @if($isEdit)
            Tỷ lệ đã ghi nhận trên hồ sơ này — không chỉnh sửa tại đây. Thay đổi tỷ lệ chung tại <a href="{{ route('accountant.insurance.rates.index') }}" class="font-semibold text-sky-700 hover:underline">Quản lý tỷ lệ BH</a> (chỉ áp dụng hồ sơ mới).
        @else
            Tỷ lệ được khóa theo cấu hình hiện tại. Chỉnh sửa tại <a href="{{ route('accountant.insurance.rates.index') }}" class="font-semibold text-sky-700 hover:underline">Quản lý tỷ lệ BH</a>.
        @endif
    </p>
</div>

<div class="mt-4">
    <label class="accountant-label">Ghi chú</label>
    <textarea name="note" rows="3" class="accountant-field">{{ old('note', $insurance->note ?? '') }}</textarea>
</div>

@push('head')
<script>
async function loadSuggestedSalary(employeeId) {
    if (!employeeId) return;
    try {
        const res = await fetch(`{{ url('accountant/insurance/suggest-salary') }}/${employeeId}`);
        const data = await res.json();
        const input = document.getElementById('contribution_salary');
        if (input && !input.value && data.salary) input.value = data.salary;
    } catch (e) {}
}

document.addEventListener('DOMContentLoaded', () => {
    const selectedEmployeeId = @json($selectedEmployee?->id);
    if (selectedEmployeeId) {
        loadSuggestedSalary(selectedEmployeeId);
    }
});
</script>
@endpush
