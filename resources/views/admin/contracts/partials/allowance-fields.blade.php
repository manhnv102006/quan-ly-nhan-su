@php
    $allowanceValues = $allowanceValues ?? [];
    $positions = $positions ?? collect();
    $positionAllowanceMap = $positions->mapWithKeys(
        fn ($position) => [(int) $position->id => (int) $position->allowance]
    )->all();
@endphp

<div class="mt-2 rounded-2xl border border-violet-100 bg-violet-50/40 p-4 sm:p-5">
    <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
        <div>
            <h4 class="text-sm font-bold text-slate-800">Phụ cấp theo hợp đồng</h4>
            <p class="text-xs text-slate-500">Nhập số tiền cho từng loại phụ cấp áp dụng. Để trống nghĩa là không có khoản phụ cấp đó.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            @if($allowanceTypes->isNotEmpty())
                <button type="button" data-allowance-fill-default
                        class="inline-flex items-center gap-1.5 rounded-xl bg-violet-600 px-3 py-2 text-xs font-semibold text-white shadow-sm hover:bg-violet-700">
                    Điền phụ cấp mặc định
                </button>
                <button type="button" data-allowance-clear
                        class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                    Xóa hết
                </button>
            @endif
            <a href="{{ route('admin.allowance-types.index') }}" class="text-xs font-semibold text-violet-600 hover:text-violet-700">
                Quản lý loại phụ cấp →
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        @forelse($allowanceTypes as $type)
            @php
                $value = old('allowances.'.$type->id, $allowanceValues[$type->id] ?? null);
            @endphp
            <div>
                <label for="allowance_{{ $type->id }}" class="admin-label">
                    {{ $type->name }}
                    @if($type->calculation_note)
                        <span class="font-normal text-slate-400">· {{ $type->calculation_note }}</span>
                    @endif
                </label>
                <input
                    type="text"
                    id="allowance_{{ $type->id }}"
                    name="allowances[{{ $type->id }}]"
                    class="admin-field allowance-input"
                    inputmode="numeric"
                    data-allowance-code="{{ $type->code }}"
                    data-default-amount="{{ (int) $type->default_amount }}"
                    @if($type->isPositionAllowance())
                        placeholder="Mặc định theo chức vụ đã chọn (để trống nếu không áp dụng)"
                    @else
                        placeholder="Mặc định: {{ number_format((float) $type->default_amount, 0, ',', '.') }} (để trống nếu không áp dụng)"
                    @endif
                    value="{{ is_numeric($value) ? number_format((float) $value, 0, ',', '.') : $value }}"
                >
                @error('allowances.'.$type->id)<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>
        @empty
            <div class="md:col-span-2 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                Chưa có loại phụ cấp. <a href="{{ route('admin.allowance-types.create') }}" class="font-semibold underline">Thêm loại phụ cấp</a> trước.
            </div>
        @endforelse
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const allowanceInputs = document.querySelectorAll('.allowance-input');
            const positionAllowanceInput = document.querySelector('.allowance-input[data-allowance-code="position"]');
            const positionAllowances = @json($positionAllowanceMap);
            const employeeSelect = document.querySelector('[data-employee-select]');
            const positionSelect = document.querySelector('[data-position-select]');
            const positionInput = document.querySelector('[data-position-input]');

            function formatMoney(value) {
                const digits = (value || '').toString().replace(/\D/g, '');
                if (digits === '') return '';
                return digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            }

            function parseMoney(value) {
                return parseInt((value || '').toString().replace(/\D/g, '') || '0', 10);
            }

            function currentPositionId() {
                if (employeeSelect && employeeSelect.value) {
                    const option = employeeSelect.selectedOptions[0];
                    if (option && option.dataset.positionId) {
                        return option.dataset.positionId;
                    }
                }

                if (positionSelect && positionSelect.value) {
                    return positionSelect.value;
                }

                return positionInput ? positionInput.value : '';
            }

            function positionAmount(positionId) {
                if (!positionId) return 0;
                return parseInt(positionAllowances[positionId] || 0, 10);
            }

            let lastAutoAmount = positionAmount(currentPositionId());

            function syncPositionAllowance(force) {
                if (!positionAllowanceInput) return;

                const amount = positionAmount(currentPositionId());
                const current = parseMoney(positionAllowanceInput.value);
                const canOverwrite = force
                    || current === 0
                    || (lastAutoAmount !== null && current === lastAutoAmount);

                if (!canOverwrite) {
                    lastAutoAmount = amount;
                    return;
                }

                positionAllowanceInput.value = amount > 0 ? formatMoney(amount) : '';
                lastAutoAmount = amount;
            }

            allowanceInputs.forEach(function (input) {
                input.value = formatMoney(input.value);

                input.addEventListener('input', function () {
                    this.value = formatMoney(this.value);
                });

                const form = input.closest('form');
                if (form) {
                    form.addEventListener('submit', function () {
                        input.value = (input.value || '').replace(/\D/g, '');
                    });
                }
            });

            if (employeeSelect) {
                if (currentPositionId()) {
                    syncPositionAllowance(false);
                }
                employeeSelect.addEventListener('change', function () {
                    syncPositionAllowance(false);
                });
            }

            if (positionSelect && positionSelect.offsetParent !== null) {
                positionSelect.addEventListener('change', function () {
                    syncPositionAllowance(false);
                });
            }

            const fillDefaultButton = document.querySelector('[data-allowance-fill-default]');
            if (fillDefaultButton) {
                fillDefaultButton.addEventListener('click', function () {
                    allowanceInputs.forEach(function (input) {
                        const isPosition = input.dataset.allowanceCode === 'position';
                        const amount = isPosition
                            ? positionAmount(currentPositionId())
                            : parseInt(input.dataset.defaultAmount || '0', 10);
                        input.value = amount > 0 ? formatMoney(amount) : '';
                    });
                    lastAutoAmount = positionAmount(currentPositionId());
                });
            }

            const clearButton = document.querySelector('[data-allowance-clear]');
            if (clearButton) {
                clearButton.addEventListener('click', function () {
                    allowanceInputs.forEach(function (input) {
                        input.value = '';
                    });
                    lastAutoAmount = 0;
                });
            }
        });
    </script>
@endpush
