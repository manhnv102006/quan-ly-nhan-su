@php
    $user = Auth::user();
    $role = $user->role->name ?? 'employee';
    $formatMoney = fn ($n) => number_format((float) $n, 0, ',', '.') . '₫';

    $layout = match ($role) {
        'manager' => 'manager-layout',
        'leader' => 'leader-layout',
        'accountant' => 'accountant-layout',
        default => 'employee-layout',
    };

    $layoutParams = [
        'title' => 'Gửi yêu cầu ứng lương',
        'subtitle' => 'Kế toán duyệt · Trừ vào lương thực lĩnh',
    ];
@endphp

<x-dynamic-component :component="$layout" :attributes="new \Illuminate\View\ComponentAttributeBag($layoutParams)">
    <div class="max-w-2xl space-y-6">
        <a href="{{ route('employee.advances.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-800">
            ← Quay lại danh sách
        </a>

        <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm sm:p-8">
            <h2 class="mb-2 text-lg font-bold text-slate-800">Yêu cầu ứng lương mới</h2>
            <p class="mb-6 text-sm text-slate-500">
                Hạn mức tối đa: <strong class="text-cyan-700">{{ $formatMoney($maxAdvanceAmount) }}</strong>
                @if($referenceSalary > 0)
                    (50% lương tham chiếu {{ $formatMoney($referenceSalary) }})
                @endif
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

            <form method="POST" action="{{ route('employee.advances.store') }}" class="space-y-5">
                @csrf

                <div>
                    <label class="mb-2 block text-xs font-bold uppercase text-slate-500">Số tiền ứng <span class="text-rose-500">*</span></label>
                    <input type="number" name="amount" min="100000" max="{{ $maxAdvanceAmount }}" step="100000" required
                           value="{{ old('amount') }}"
                           class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20"
                           placeholder="VD: 2000000">
                    <p class="mt-1 text-xs text-slate-400">Tối thiểu 100.000₫</p>
                </div>

                <div>
                    <label class="mb-2 block text-xs font-bold uppercase text-slate-500">Ngày cần ứng <span class="text-rose-500">*</span></label>
                    <input type="date" name="request_date" required value="{{ old('request_date', now()->format('Y-m-d')) }}"
                           class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20">
                </div>

                <div>
                    <label class="mb-2 block text-xs font-bold uppercase text-slate-500">Lý do ứng lương <span class="text-rose-500">*</span></label>
                    <textarea name="reason" rows="4" required maxlength="1000"
                              class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20"
                              placeholder="VD: Chi phí y tế đột xuất, học phí con...">{{ old('reason') }}</textarea>
                </div>

                <div>
                    <label class="mb-2 block text-xs font-bold uppercase text-slate-500">Ghi chú thêm</label>
                    <textarea name="note" rows="2" maxlength="2000"
                              class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20">{{ old('note') }}</textarea>
                </div>

                <div class="rounded-xl border border-amber-100 bg-amber-50/70 px-4 py-3 text-xs text-amber-900">
                    Sau khi kế toán duyệt, khoản ứng sẽ được <strong>khấu trừ vào mục khấu trừ / lương thực lĩnh</strong> khi tính lương kỳ tới.
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit" class="rounded-xl bg-cyan-600 px-5 py-2.5 text-sm font-semibold text-white shadow-md hover:bg-cyan-700">
                        Gửi tới kế toán
                    </button>
                    <a href="{{ route('employee.advances.index') }}" class="rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50">Hủy</a>
                </div>
            </form>
        </div>
    </div>
</x-dynamic-component>
