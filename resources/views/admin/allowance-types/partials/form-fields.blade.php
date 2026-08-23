@php
    $isPositionType = isset($allowanceType) && $allowanceType->isPositionAllowance();
    $positionsForAllowance = $positionsForAllowance ?? collect();
@endphp

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

    @unless($isPositionType)
        <div>
            <label for="default_amount" class="admin-label">Mức mặc định *</label>
            <input type="text" id="default_amount" name="default_amount" class="admin-field money-input" inputmode="numeric" required
                   value="{{ old('default_amount', isset($allowanceType) ? (int) $allowanceType->default_amount : '') }}">
            @error('default_amount')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
        </div>
    @endunless

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

@if($isPositionType)
    <div class="mt-6 rounded-2xl border border-violet-100 bg-violet-50/40 p-4 sm:p-5">
        <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
            <div>
                <h3 class="text-sm font-bold text-slate-800">Mức theo chức vụ</h3>
                <p class="mt-1 text-xs text-slate-500">
                    Mỗi chức vụ có mức riêng. Số này tự điền khi tạo hợp đồng; đổi ở đây không sửa hợp đồng đang hiệu lực.
                </p>
            </div>
            <a href="{{ route('admin.positions') }}" class="text-xs font-semibold text-violet-600 hover:text-violet-700">
                Quản lý chức vụ →
            </a>
        </div>

        @if($positionsForAllowance->isEmpty())
            <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                Chưa có chức vụ đang dùng.
                <a href="{{ route('admin.positions.create') }}" class="font-semibold underline">Thêm chức vụ</a>
                rồi quay lại gán mức phụ cấp.
            </div>
        @else
            <div class="overflow-x-auto rounded-xl border border-violet-100 bg-white">
                <table class="w-full min-w-[480px]">
                    <thead>
                        <tr class="bg-slate-50 text-left text-xs font-bold uppercase text-slate-500">
                            <th class="px-4 py-3">Chức vụ</th>
                            <th class="px-4 py-3 w-56">Phụ cấp chức vụ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($positionsForAllowance as $position)
                            @php
                                $oldAmount = old('position_allowances.'.$position->id, (int) $position->allowance);
                            @endphp
                            <tr>
                                <td class="px-4 py-3">
                                    <p class="font-semibold text-slate-800">{{ $position->position_name }}</p>
                                    @if($position->description)
                                        <p class="mt-0.5 text-xs text-slate-500">{{ $position->description }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <input
                                        type="text"
                                        name="position_allowances[{{ $position->id }}]"
                                        class="admin-field money-input position-allowance-input"
                                        inputmode="numeric"
                                        value="{{ is_numeric($oldAmount) ? number_format((float) $oldAmount, 0, ',', '.') : $oldAmount }}"
                                        placeholder="0"
                                    >
                                    @error('position_allowances.'.$position->id)
                                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                                    @enderror
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @error('position_allowances')
                <p class="mt-2 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        @endif
    </div>
@endif

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const inputs = document.querySelectorAll('#default_amount, .position-allowance-input');
            if (!inputs.length) return;

            function formatMoney(value) {
                const digits = (value || '').toString().replace(/\D/g, '');
                if (digits === '') return '';
                return digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            }

            inputs.forEach(function (input) {
                input.value = formatMoney(input.value);
                input.addEventListener('input', function () { this.value = formatMoney(this.value); });
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
