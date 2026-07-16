@php
    $isEdit = isset($insurance);
    $rates = $rates ?? [];
    $selectedEmployee = $selectedEmployee ?? null;
    $rateLimits = \App\Models\EmployeeInsurance::rateLimitsPercent();
    $bhtnDefaultPct = round(($rates['bhtn_employee_rate'] ?? 0.01) * 100, 2);
    $bhtnValue = old('bhtn_rate', $isEdit
        ? round((float) $insurance->bhtn_employee_rate * 100, 2)
        : $bhtnDefaultPct);
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
    <p class="mb-3 text-xs font-bold uppercase text-sky-800">Tỷ lệ đóng bảo hiểm (%)</p>
    <div id="insurance-rate-errors" class="mb-3 hidden rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"></div>
    <div class="grid grid-cols-2 gap-3 md:grid-cols-3">
        @foreach([
            ['bhxh_employee_rate', 'BHXH NLĐ', 8],
            ['bhxh_employer_rate', 'BHXH DN', 17.5],
            ['bhyt_employee_rate', 'BHYT NLĐ', 1.5],
            ['bhyt_employer_rate', 'BHYT DN', 3],
        ] as [$field, $label, $defaultPct])
            @php $maxPct = $rateLimits[$field]['max']; @endphp
            <div>
                <label class="text-xs font-semibold text-slate-600">{{ $label }}</label>
                <input
                    type="number"
                    name="{{ $field }}"
                    data-rate-label="{{ $label }}"
                    data-rate-max="{{ $maxPct }}"
                    min="0"
                    max="{{ $maxPct }}"
                    step="0.01"
                    required
                    class="insurance-rate-field accountant-field mt-1"
                    value="{{ old($field, isset($insurance) ? round((float) $insurance->$field * 100, 2) : round(($rates[$field] ?? $defaultPct / 100) * 100, 2)) }}"
                >
                <p class="mt-1 text-[10px] text-slate-400">Tối đa {{ $maxPct }}%</p>
            </div>
        @endforeach
        <div>
            <label class="text-xs font-semibold text-slate-600">BHTN (NLĐ &amp; DN)</label>
            <input
                type="number"
                name="bhtn_rate"
                data-rate-label="{{ $rateLimits['bhtn_rate']['label'] }}"
                data-rate-max="{{ $rateLimits['bhtn_rate']['max'] }}"
                min="0"
                max="{{ $rateLimits['bhtn_rate']['max'] }}"
                step="0.01"
                required
                class="insurance-rate-field accountant-field mt-1"
                value="{{ $bhtnValue }}"
            >
            <p class="mt-1 text-[10px] text-slate-400">Áp dụng cho cả NLĐ và DN · tối đa {{ $rateLimits['bhtn_rate']['max'] }}%</p>
        </div>
    </div>
    <p class="mt-2 text-xs text-slate-500">Nhập theo % (vd: 8 = 8%). Mặc định theo quy định VN. Hệ thống chặn tỷ lệ vượt ngưỡng để tránh nhập nhầm.</p>
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

function validateInsuranceRates(showBox = true) {
    const fields = document.querySelectorAll('.insurance-rate-field');
    const errors = [];

    fields.forEach((input) => {
        const label = input.dataset.rateLabel || input.name;
        const max = parseFloat(input.dataset.rateMax);
        const value = parseFloat(input.value);

        input.classList.remove('border-rose-400', 'ring-2', 'ring-rose-200');

        if (input.value === '' || Number.isNaN(value)) {
            errors.push(`Tỷ lệ ${label} là bắt buộc.`);
            input.classList.add('border-rose-400', 'ring-2', 'ring-rose-200');
            return;
        }

        if (value < 0) {
            errors.push(`Tỷ lệ ${label} không được âm.`);
            input.classList.add('border-rose-400', 'ring-2', 'ring-rose-200');
            return;
        }

        if (!Number.isNaN(max) && value > max) {
            errors.push(`${label} không được vượt quá ${max}%.`);
            input.classList.add('border-rose-400', 'ring-2', 'ring-rose-200');
        }
    });

    const box = document.getElementById('insurance-rate-errors');
    if (!box) return errors.length === 0;

    if (errors.length && showBox) {
        box.innerHTML = '<ul class="list-disc pl-5">' + errors.map((e) => `<li>${e}</li>`).join('') + '</ul>';
        box.classList.remove('hidden');
    } else {
        box.classList.add('hidden');
        box.innerHTML = '';
    }

    return errors.length === 0;
}

document.addEventListener('DOMContentLoaded', () => {
    const selectedEmployeeId = @json($selectedEmployee?->id);
    if (selectedEmployeeId) {
        loadSuggestedSalary(selectedEmployeeId);
    }

    document.querySelectorAll('.insurance-rate-field').forEach((input) => {
        input.addEventListener('input', () => validateInsuranceRates(false));
        input.addEventListener('blur', () => validateInsuranceRates(true));
    });

    const form = document.querySelector('form[action*="insurance"]');
    if (form) {
        form.addEventListener('submit', (event) => {
            if (!validateInsuranceRates(true)) {
                event.preventDefault();
                document.getElementById('insurance-rate-errors')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
    }
});
</script>
@endpush
