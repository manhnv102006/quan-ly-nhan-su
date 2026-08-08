@php
    $rateLimits = \App\Models\EmployeeInsurance::rateLimitsPercent();
    $rates = $rates ?? [];
    $bhtnDefaultPct = round(($rates['bhtn_employee_rate'] ?? 0.01) * 100, 2);
    $bhtnValue = old('bhtn_rate', $bhtnDefaultPct);
@endphp

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
                value="{{ old($field, round(($rates[$field] ?? $defaultPct / 100) * 100, 2)) }}"
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
<p class="mt-2 text-xs text-slate-500">Nhập theo % (vd: 8 = 8%). Thay đổi tại đây chỉ áp dụng cho <strong>hồ sơ BH mới</strong>; hồ sơ đã tạo giữ nguyên tỷ lệ đã ghi nhận.</p>

@push('head')
<script>
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
    document.querySelectorAll('.insurance-rate-field').forEach((input) => {
        input.addEventListener('input', () => validateInsuranceRates(false));
        input.addEventListener('blur', () => validateInsuranceRates(true));
    });

    const form = document.getElementById('insurance-rates-form');
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
