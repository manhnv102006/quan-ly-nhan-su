@php
    $allowanceValues = $allowanceValues ?? [];
@endphp

<div class="mt-2 rounded-2xl border border-violet-100 bg-violet-50/40 p-4 sm:p-5">
    <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
        <div>
            <h4 class="text-sm font-bold text-slate-800">Phụ cấp theo hợp đồng</h4>
            <p class="text-xs text-slate-500">Nhập số tiền cho từng loại phụ cấp áp dụng. Để trống nghĩa là không có khoản phụ cấp đó.</p>
        </div>
        <a href="{{ route('admin.allowance-types.index') }}" class="text-xs font-semibold text-violet-600 hover:text-violet-700">
            Quản lý loại phụ cấp →
        </a>
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
                    placeholder="Mặc định: {{ number_format((float) $type->default_amount, 0, ',', '.') }} (để trống nếu không áp dụng)"
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

            function formatMoney(value) {
                const digits = (value || '').toString().replace(/\D/g, '');
                if (digits === '') return '';
                return digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
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
        });
    </script>
@endpush
