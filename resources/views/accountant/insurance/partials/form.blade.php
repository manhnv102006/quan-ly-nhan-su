@php
    $isEdit = isset($insurance);
    $rates = $rates ?? [];
@endphp

<div class="grid grid-cols-1 gap-4 md:grid-cols-2">
    @if(!$isEdit)
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
    <p class="mb-3 text-xs font-bold uppercase text-sky-800">Tỷ lệ đóng bảo hiểm (%)</p>
    <div class="grid grid-cols-2 gap-3 md:grid-cols-3">
        @foreach([
            ['bhxh_employee_rate', 'BHXH NLĐ', 8],
            ['bhxh_employer_rate', 'BHXH DN', 17.5],
            ['bhyt_employee_rate', 'BHYT NLĐ', 1.5],
            ['bhyt_employer_rate', 'BHYT DN', 3],
            ['bhtn_employee_rate', 'BHTN NLĐ', 1],
            ['bhtn_employer_rate', 'BHTN DN', 1],
        ] as [$field, $label, $defaultPct])
            <div>
                <label class="text-xs font-semibold text-slate-600">{{ $label }}</label>
                <input type="number" name="{{ $field }}" min="0" max="100" step="0.01" class="accountant-field mt-1"
                       value="{{ old($field, isset($insurance) ? round((float)$insurance->$field * 100, 2) : round(($rates[$field] ?? $defaultPct / 100) * 100, 2)) }}">
            </div>
        @endforeach
    </div>
    <p class="mt-2 text-xs text-slate-500">Nhập theo % (vd: 8 = 8%). Mặc định theo quy định VN.</p>
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
</script>
@endpush
