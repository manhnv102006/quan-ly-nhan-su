@php
    $role = Auth::user()->role->name ?? 'employee';

    $layout = \App\Support\SelfServiceLayout::component($role);

    $layoutParams = [
        'title' => 'Đăng ký NPT mới',
        'subtitle' => 'Kế toán duyệt · Áp dụng GT phụ thuộc khi được chấp nhận',
    ];
@endphp

<x-dynamic-component :component="$layout" :attributes="new \Illuminate\View\ComponentAttributeBag($layoutParams)">
    <div class="max-w-2xl space-y-6">
        <a href="{{ route('employee.tax-dependents.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-800">
            ← Quay lại danh sách
        </a>

        <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm sm:p-8">
            <h2 class="mb-2 text-lg font-bold text-slate-800">Thông tin người phụ thuộc</h2>
            <p class="mb-6 text-sm text-slate-500">
                Giảm trừ mặc định: <strong class="text-violet-700">{{ number_format($defaultDeduction, 0, ',', '.') }}₫/tháng</strong> sau khi kế toán duyệt.
            </p>

            @if($errors->any())
                <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    <ul class="list-disc pl-5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('employee.tax-dependents.store') }}" class="space-y-5">
                @csrf

                <div>
                    <label class="mb-2 block text-xs font-bold uppercase text-slate-500">Họ tên người phụ thuộc <span class="text-rose-500">*</span></label>
                    <input type="text" name="full_name" required maxlength="255" value="{{ old('full_name') }}"
                           class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none transition focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20"
                           placeholder="VD: Nguyễn Văn A">
                </div>

                <div>
                    <label class="mb-2 block text-xs font-bold uppercase text-slate-500">Quan hệ <span class="text-rose-500">*</span></label>
                    <select name="relationship" required
                            class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none transition focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20">
                        @foreach($relationshipLabels as $val => $label)
                            <option value="{{ $val }}" @selected(old('relationship') === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase text-slate-500">Ngày sinh</label>
                        <input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}"
                               class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none transition focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20">
                    </div>
                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase text-slate-500">CCCD/CMND</label>
                        <input type="text" name="id_number" maxlength="30" value="{{ old('id_number') }}"
                               class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none transition focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20">
                    </div>
                </div>

                <div>
                    <label class="mb-2 block text-xs font-bold uppercase text-slate-500">Ngày bắt đầu giảm trừ <span class="text-rose-500">*</span></label>
                    <input type="date" name="start_date" required value="{{ old('start_date', now()->format('Y-m-d')) }}"
                           class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none transition focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20">
                </div>

                <div>
                    <label class="mb-2 block text-xs font-bold uppercase text-slate-500">Ghi chú</label>
                    <textarea name="note" rows="3" maxlength="1000"
                              class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none transition focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20"
                              placeholder="VD: Con đang đi học, đính kèm giấy khai sinh...">{{ old('note') }}</textarea>
                </div>

                <div class="rounded-xl border border-amber-100 bg-amber-50/70 px-4 py-3 text-xs text-amber-900">
                    Yêu cầu sẽ được gửi tới <strong>kế toán</strong>. Khi duyệt, NPT được tính vào <strong>GT phụ thuộc</strong> và áp dụng ngay khi tính thuế TNCN / lương.
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit" class="rounded-xl bg-violet-600 px-5 py-2.5 text-sm font-semibold text-white shadow-md hover:bg-violet-700">
                        Gửi tới kế toán
                    </button>
                    <a href="{{ route('employee.tax-dependents.index') }}" class="rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50">Hủy</a>
                </div>
            </form>
        </div>
    </div>
</x-dynamic-component>
