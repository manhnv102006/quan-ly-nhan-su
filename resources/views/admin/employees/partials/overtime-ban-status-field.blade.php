<div class="md:col-span-2">
    <label class="block text-sm font-medium text-slate-700">Cấm tăng ca (Điều 137 BLLĐ)</label>
    <select name="overtime_ban_status"
            class="mt-1 w-full rounded-xl border px-4 py-3 text-slate-800 text-sm @error('overtime_ban_status') border-rose-400 @else border-slate-200 @enderror">
        <option value="">Không áp dụng</option>
        @foreach (\App\Models\Employee::OT_BAN_LABELS as $value => $label)
            <option value="{{ $value }}" @selected(old('overtime_ban_status', $employee->overtime_ban_status ?? null) === $value)>{{ $label }}</option>
        @endforeach
    </select>
    <p class="mt-1.5 text-xs text-slate-500 leading-relaxed">
        Chỉ đánh dấu cho lao động nữ: mang thai từ tháng thứ 7 hoặc đang nuôi con dưới 12 tháng tuổi. Nhân viên bị cấm mọi đơn tăng ca.
    </p>
    @error('overtime_ban_status') <span class="mt-1 block text-red-600 text-xs">{{ $message }}</span> @enderror
</div>
