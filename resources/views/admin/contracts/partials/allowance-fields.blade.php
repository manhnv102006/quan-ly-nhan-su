@php
    $allowanceValues = $allowanceValues ?? [];
    $positionsData = ($positions ?? collect())->mapWithKeys(fn ($pos) => [$pos->id => (int) $pos->allowance])->toArray();
@endphp

<div class="mt-2 rounded-2xl border border-violet-100 bg-violet-50/40 p-4 sm:p-5">
    <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
        <div>
            <h4 class="text-sm font-bold text-slate-800">Phụ cấp theo hợp đồng</h4>
            <p class="text-xs text-slate-500">Chọn từ danh mục loại phụ cấp — mặc định tự điền, có thể điều chỉnh.</p>
        </div>
        <a href="{{ route('admin.allowance-types.index') }}" class="text-xs font-semibold text-violet-600 hover:text-violet-700">
            Quản lý loại phụ cấp →
        </a>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        @forelse($allowanceTypes as $type)
            @php
                $value = old('allowances.'.$type->id, $allowanceValues[$type->id] ?? $type->default_amount);
                $isFixed = $type->code === \App\Models\AllowanceType::CODE_FIXED;
                $isPosition = $type->code === \App\Models\AllowanceType::CODE_POSITION;
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
                    class="admin-field allowance-input {{ $isFixed ? 'bg-slate-50 cursor-not-allowed' : '' }}"
                    inputmode="numeric"
                    data-allowance-code="{{ $type->code }}"
                    data-default="{{ (int) $type->default_amount }}"
                    value="{{ is_numeric($value) ? number_format((float) $value, 0, ',', '.') : $value }}"
                    {{ $isFixed ? 'readonly' : '' }}
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
            const typeSelect = document.querySelector('[data-contract-type-select]');
            const positionSelect = document.querySelector('[data-position-select]');
            const allowanceInputs = document.querySelectorAll('.allowance-input');
            const positions = @json($positionsData);

            function formatMoney(value) {
                const digits = (value || '').toString().replace(/\D/g, '');
                if (digits === '') return '0';
                return digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            }

            function syncFixedAllowance() {
                const fixedInput = document.querySelector('[data-allowance-code="fixed"]');
                if (!fixedInput || !typeSelect) return;
                const opt = typeSelect.selectedOptions[0];
                const isInternship = opt && opt.dataset.internship === '1';
                fixedInput.value = isInternship ? '0' : formatMoney('1500000');

                allowanceInputs.forEach(function (input) {
                    if (input.dataset.allowanceCode === 'fixed') return;
                    input.readOnly = isInternship;
                    if (isInternship) {
                        input.value = '0';
                        input.classList.add('bg-slate-50');
                    } else if (input.dataset.allowanceCode !== 'fixed') {
                        input.readOnly = false;
                        input.classList.remove('bg-slate-50');
                    }
                });
            }

            function syncPositionAllowance() {
                const positionInput = document.querySelector('[data-allowance-code="position"]');
                if (!positionInput || !positionSelect) return;
                const posId = positionSelect.value;
                if (!posId || positionInput.dataset.userEdited === '1') return;
                const amount = positions[posId] ?? positionInput.dataset.default ?? 0;
                positionInput.value = formatMoney(String(amount));
            }

            allowanceInputs.forEach(function (input) {
                input.addEventListener('input', function () {
                    if (this.dataset.allowanceCode === 'position') {
                        this.dataset.userEdited = '1';
                    }
                    this.value = formatMoney(this.value);
                });

                const form = input.closest('form');
                if (form) {
                    form.addEventListener('submit', function () {
                        input.value = (input.value || '').replace(/\D/g, '');
                    });
                }
            });

            if (typeSelect) {
                typeSelect.addEventListener('change', syncFixedAllowance);
                syncFixedAllowance();
            }

            if (positionSelect) {
                positionSelect.addEventListener('change', function () {
                    const positionInput = document.querySelector('[data-allowance-code="position"]');
                    if (positionInput) positionInput.dataset.userEdited = '0';
                    syncPositionAllowance();
                });
                syncPositionAllowance();
            }
        });
    </script>
@endpush
