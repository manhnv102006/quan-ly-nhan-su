<div class="grid grid-cols-1 gap-4 md:grid-cols-2">
    <div>
        <label for="name" class="admin-label">Tên phụ cấp *</label>
        <input type="text" id="name" name="name" class="admin-field" required
               value="{{ old('name', $allowanceType->name ?? '') }}">
        @error('name')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="code" class="admin-label">Mã (code) *</label>
        <input type="text" id="code" name="code" class="admin-field {{ ($allowanceType->is_system ?? false) ? 'bg-slate-50' : '' }}"
               value="{{ old('code', $allowanceType->code ?? '') }}"
               @if($allowanceType->is_system ?? false) readonly @endif required>
        @error('code')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="default_amount" class="admin-label">Mức mặc định *</label>
        <input type="text" id="default_amount" name="default_amount" class="admin-field money-input" inputmode="numeric" required
               value="{{ old('default_amount', isset($allowanceType) ? (int) $allowanceType->default_amount : '') }}">
        @error('default_amount')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="calculation_type" class="admin-label">Cách tính khi lên bảng lương *</label>
        <select id="calculation_type" name="calculation_type" class="admin-field" required>
            @foreach(\App\Models\AllowanceType::CALC_LABELS as $calcValue => $calcLabel)
                <option value="{{ $calcValue }}"
                    @selected(old('calculation_type', $allowanceType->calculation_type ?? \App\Models\AllowanceType::CALC_PRORATA) === $calcValue)>
                    {{ $calcLabel }}
                </option>
            @endforeach
        </select>
        @error('calculation_type')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="sort_order" class="admin-label">Thứ tự hiển thị</label>
        <input type="number" id="sort_order" name="sort_order" class="admin-field" min="0" max="999"
               value="{{ old('sort_order', $allowanceType->sort_order ?? 0) }}">
        @error('sort_order')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div class="md:col-span-2">
        <label for="calculation_note" class="admin-label">Ghi chú tính lương</label>
        <input type="text" id="calculation_note" name="calculation_note" class="admin-field"
               value="{{ old('calculation_note', $allowanceType->calculation_note ?? '') }}"
               placeholder="VD: Trả cố định hàng tháng">
        @error('calculation_note')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div class="md:col-span-2">
        <label for="description" class="admin-label">Mô tả</label>
        <textarea id="description" name="description" rows="2" class="admin-field">{{ old('description', $allowanceType->description ?? '') }}</textarea>
        @error('description')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="inline-flex items-center gap-2 text-sm font-medium text-slate-700">
            <input type="checkbox" name="is_active" value="1" class="rounded border-slate-300 text-violet-600"
                   @checked(old('is_active', $allowanceType->is_active ?? true))>
            Đang sử dụng
        </label>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const input = document.getElementById('default_amount');
            if (!input) return;
            function formatMoney(value) {
                const digits = (value || '').toString().replace(/\D/g, '');
                if (digits === '') return '';
                return digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            }
            input.value = formatMoney(input.value);
            input.addEventListener('input', function () { this.value = formatMoney(this.value); });
            const form = input.closest('form');
            if (form) {
                form.addEventListener('submit', function () {
                    input.value = (input.value || '').replace(/\D/g, '');
                });
            }
        });
    </script>
@endpush
