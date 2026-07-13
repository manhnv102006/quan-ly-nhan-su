@php($type = $contractType ?? null)
<div>
    <label for="contract_name" class="block text-sm font-semibold text-slate-700">Tên loại hợp đồng</label>
    <input type="text" id="contract_name" name="contract_name" value="{{ old('contract_name', $type?->contract_name) }}"
           class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm" required>
    @error('contract_name') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
</div>
<div>
    <label for="code" class="block text-sm font-semibold text-slate-700">Mã (code)</label>
    <input type="text" id="code" name="code" value="{{ old('code', $type?->code) }}"
           class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm">
</div>
<div>
    <label for="category" class="block text-sm font-semibold text-slate-700">Nhóm loại</label>
    <select id="category" name="category" class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm" required>
        @foreach(\App\Models\ContractType::CATEGORY_LABELS as $value => $label)
            <option value="{{ $value }}" @selected(old('category', $type?->category ?? 'fixed') === $value)>{{ $label }}</option>
        @endforeach
    </select>
</div>
<div>
    <label for="duration_month" class="block text-sm font-semibold text-slate-700">Thời hạn mặc định (tháng)</label>
    <input type="number" id="duration_month" name="duration_month" value="{{ old('duration_month', $type?->duration_month ?? 12) }}"
           class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm" min="0" required>
    <p class="mt-1 text-xs text-slate-400">Nhập 0 cho HĐ không xác định thời hạn.</p>
</div>
<div>
    <label for="description" class="block text-sm font-semibold text-slate-700">Mô tả</label>
    <textarea id="description" name="description" rows="2" class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm">{{ old('description', $type?->description) }}</textarea>
</div>
